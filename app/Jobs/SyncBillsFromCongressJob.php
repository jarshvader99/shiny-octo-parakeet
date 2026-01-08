<?php

namespace App\Jobs;

use App\Models\Bill;
use App\Models\BillActor;
use App\Models\BillEvent;
use App\Models\BillVersion;
use App\Services\CongressApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncBillsFromCongressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?int $congressNumber = null,
        public ?string $billType = null,
        public int $limit = 20,
        public int $offset = 0
    ) {
        // Use current Congress if not specified
        $this->congressNumber = $congressNumber ?? app(CongressApiClient::class)->getCurrentCongress();
    }

    /**
     * Execute the job.
     */
    public function handle(CongressApiClient $api): void
    {
        Log::info('Starting bill sync from Congress.gov', [
            'congress' => $this->congressNumber,
            'bill_type' => $this->billType,
            'limit' => $this->limit,
            'offset' => $this->offset,
        ]);

        try {
            // Fetch bills from Congress.gov API
            $response = $api->getBills($this->congressNumber, $this->offset, $this->limit);

            if (empty($response['bills'])) {
                Log::warning('No bills returned from API', [
                    'congress' => $this->congressNumber,
                ]);
                return;
            }

            $synced = 0;
            foreach ($response['bills'] as $billData) {
                $this->syncBill($api, $billData);
                $synced++;
            }

            Log::info('Bill sync completed successfully', [
                'synced' => $synced,
                'total_available' => $response['pagination']['count'] ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Bill sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync a single bill from API data
     */
    private function syncBill(CongressApiClient $api, array $billData): void
    {
        $billType = strtolower($billData['type'] ?? '');
        $billNumber = $billData['number'] ?? 0;

        // Skip reserved bills (usually H.R. 1-10, S. 1-10)
        $title = $billData['title'] ?? '';
        if (stripos($title, 'Reserved for') !== false) {
            Log::info('Skipping reserved bill', [
                'type' => $billType,
                'number' => $billNumber,
                'title' => $title,
            ]);
            return;
        }

        // Get detailed bill information
        $detailedBill = $api->getBill($this->congressNumber, $billType, $billNumber);

        if (!$detailedBill) {
            Log::warning('Could not fetch bill details', [
                'congress' => $this->congressNumber,
                'type' => $billType,
                'number' => $billNumber,
            ]);
            return;
        }

        // Determine chamber
        $chamber = $api->parseChamber($billType);

        // Fetch summaries from separate endpoint
        $summaries = $api->getBillSummaries($this->congressNumber, $billType, $billNumber);
        $summary = null;

        if (!empty($summaries)) {
            // Get the most recent summary (usually the first one)
            $latestSummary = $summaries[0] ?? null;
            $summary = $latestSummary['text'] ?? null;
        }

        // Extract constitutional authority statement (available for new bills)
        $constitutionalAuthority = $detailedBill['constitutionalAuthorityStatementText'] ?? null;

        // Fetch additional context data (committees, subjects) when available
        $committees = null;
        $subjects = null;
        $policyArea = null;

        // Get committee assignments (usually available immediately)
        $committeeData = $api->getBillCommittees($this->congressNumber, $billType, $billNumber);
        if (!empty($committeeData)) {
            $committees = collect($committeeData)->map(function ($committee) {
                return [
                    'name' => $committee['name'] ?? null,
                    'chamber' => $committee['chamber'] ?? null,
                    'type' => $committee['type'] ?? null,
                ];
            })->toArray();
        }

        // Get subjects/policy area (may not be available for very new bills)
        $subjectData = $api->getBillSubjects($this->congressNumber, $billType, $billNumber);
        if (!empty($subjectData)) {
            if (isset($subjectData['policyArea']['name'])) {
                $policyArea = $subjectData['policyArea']['name'];
            }
            if (isset($subjectData['legislativeSubjects']) && !empty($subjectData['legislativeSubjects'])) {
                $subjects = collect($subjectData['legislativeSubjects'])->pluck('name')->toArray();
            }
        }

        // Extract official title - look for "Official Title" or use the first title
        $officialTitle = $detailedBill['title'] ?? 'Untitled';
        $shortTitle = null;

        if (isset($detailedBill['titles']) && is_array($detailedBill['titles'])) {
            foreach ($detailedBill['titles'] as $titleObj) {
                if (isset($titleObj['titleType']) && $titleObj['titleType'] === 'Short Title(s) as Introduced') {
                    $shortTitle = $titleObj['title'];
                    break;
                } elseif (isset($titleObj['titleType']) && $titleObj['titleType'] === 'Short Title(s)') {
                    $shortTitle = $titleObj['title'];
                }
            }
        }

        // Create or update bill
        $bill = Bill::updateOrCreate(
            [
                'congress_number' => $this->congressNumber,
                'chamber' => $chamber,
                'bill_type' => strtoupper($billType),
                'bill_number' => $billNumber,
            ],
            [
                'title' => $officialTitle,
                'short_title' => $shortTitle,
                'summary' => $summary,
                'constitutional_authority_statement' => $constitutionalAuthority,
                'committees' => $committees,
                'subjects' => $subjects,
                'policy_area' => $policyArea,
                'status' => $this->mapBillStatus($detailedBill),
                'introduced_date' => $detailedBill['introducedDate'] ?? $billData['introducedDate'] ?? now(),
                'last_action_at' => isset($detailedBill['latestAction']['actionDate'])
                    ? $detailedBill['latestAction']['actionDate']
                    : null,
                'last_action_text' => $detailedBill['latestAction']['text'] ?? null,
                'congress_gov_url' => $detailedBill['url'] ?? $billData['url'] ?? null,
                'is_national' => true, // Default - can be refined later with policy area analysis
                'last_synced_at' => now(),
                'sync_source' => 'api',
                'confidence_score' => 100,
            ]
        );

        // Sync bill actions/events
        $this->syncBillActions($api, $bill, $billType, $billNumber);

        // Sync sponsors and cosponsors
        $this->syncBillActors($api, $bill, $detailedBill, $billType, $billNumber);

        // Sync bill text versions
        $this->syncBillVersions($api, $bill, $billType, $billNumber);

        Log::info('Synced bill', [
            'id' => $bill->id,
            'identifier' => $api->parseBillIdentifier([
                'type' => $billType,
                'number' => $billNumber,
            ]),
            'title' => $bill->title,
            'has_summary' => !empty($bill->summary),
        ]);
    }

    /**
     * Map Congress.gov status to our status enum
     */
    private function mapBillStatus(array $bill): string
    {
        // This is a simplified mapping - can be enhanced based on actual API data
        $latestActionText = strtolower($bill['latestAction']['text'] ?? '');

        if (str_contains($latestActionText, 'became law') || str_contains($latestActionText, 'signed by president')) {
            return 'became_law';
        }
        if (str_contains($latestActionText, 'passed senate') && str_contains($latestActionText, 'passed house')) {
            return 'passed_both';
        }
        if (str_contains($latestActionText, 'passed senate')) {
            return 'passed_senate';
        }
        if (str_contains($latestActionText, 'passed house')) {
            return 'passed_house';
        }
        if (str_contains($latestActionText, 'reported by committee')) {
            return 'reported_by_committee';
        }
        if (str_contains($latestActionText, 'referred to') || str_contains($latestActionText, 'committee')) {
            return 'referred_to_committee';
        }
        if (str_contains($latestActionText, 'vetoed')) {
            return 'vetoed';
        }
        if (str_contains($latestActionText, 'failed')) {
            return 'failed';
        }

        return 'introduced';
    }

    /**
     * Sync bill actions/events
     */
    private function syncBillActions(CongressApiClient $api, Bill $bill, string $billType, int $billNumber): void
    {
        $actions = $api->getBillActions($this->congressNumber, $billType, $billNumber);

        foreach ($actions as $action) {
            $actionDate = $action['actionDate'] ?? null;
            if (!$actionDate) {
                continue;
            }

            BillEvent::updateOrCreate(
                [
                    'bill_id' => $bill->id,
                    'event_type' => $this->mapEventType($action),
                    'occurred_at' => $actionDate,
                ],
                [
                    'description' => $action['text'] ?? 'No description',
                    'chamber' => isset($action['sourceSystem']['code'])
                        ? ($action['sourceSystem']['code'] === '1' ? 'house' : 'senate')
                        : null,
                    'source' => 'api',
                ]
            );
        }
    }

    /**
     * Map action to event type
     */
    private function mapEventType(array $action): string
    {
        $text = strtolower($action['text'] ?? '');

        if (str_contains($text, 'introduced')) {
            return 'introduced';
        }
        if (str_contains($text, 'referred to')) {
            return 'referred_to_committee';
        }
        if (str_contains($text, 'reported')) {
            return 'reported_by_committee';
        }
        if (str_contains($text, 'passed') || str_contains($text, 'agreed to')) {
            return 'passed_chamber';
        }
        if (str_contains($text, 'vote')) {
            return 'vote';
        }
        if (str_contains($text, 'amended')) {
            return 'amended';
        }
        if (str_contains($text, 'signed by president')) {
            return 'signed_by_president';
        }
        if (str_contains($text, 'became law')) {
            return 'became_law';
        }
        if (str_contains($text, 'vetoed')) {
            return 'vetoed';
        }

        return 'other';
    }

    /**
     * Sync bill sponsors and cosponsors
     */
    private function syncBillActors(CongressApiClient $api, Bill $bill, array $detailedBill, string $billType, int $billNumber): void
    {
        // Sync primary sponsor
        if (isset($detailedBill['sponsors'][0])) {
            $sponsor = $detailedBill['sponsors'][0];

            BillActor::updateOrCreate(
                [
                    'bill_id' => $bill->id,
                    'actor_type' => 'sponsor',
                    'bioguide_id' => $sponsor['bioguideId'] ?? null,
                ],
                [
                    'name' => $sponsor['fullName'] ?? $sponsor['firstName'] . ' ' . $sponsor['lastName'],
                    'party' => $sponsor['party'] ?? null,
                    'state' => $sponsor['state'] ?? null,
                    'district' => $sponsor['district'] ?? null,
                    'is_primary' => true,
                ]
            );
        }

        // Sync cosponsors
        $cosponsors = $api->getBillCosponsors($this->congressNumber, $billType, $billNumber);

        foreach ($cosponsors as $cosponsor) {
            BillActor::updateOrCreate(
                [
                    'bill_id' => $bill->id,
                    'actor_type' => 'cosponsor',
                    'bioguide_id' => $cosponsor['bioguideId'] ?? null,
                ],
                [
                    'name' => $cosponsor['fullName'] ?? $cosponsor['firstName'] . ' ' . $cosponsor['lastName'],
                    'party' => $cosponsor['party'] ?? null,
                    'state' => $cosponsor['state'] ?? null,
                    'district' => $cosponsor['district'] ?? null,
                    'is_primary' => false,
                    'joined_at' => $cosponsor['sponsorshipDate'] ?? null,
                ]
            );
        }
    }

    /**
     * Sync bill text versions
     */
    private function syncBillVersions(CongressApiClient $api, Bill $bill, string $billType, int $billNumber): void
    {
        $textVersions = $api->getBillText($this->congressNumber, $billType, $billNumber);

        foreach ($textVersions as $version) {
            BillVersion::updateOrCreate(
                [
                    'bill_id' => $bill->id,
                    'version_code' => $version['type'] ?? 'unknown',
                ],
                [
                    'version_name' => $version['name'] ?? $version['type'] ?? 'Unknown Version',
                    'text_url' => $version['formats'][0]['url'] ?? null,
                    'published_at' => $version['date'] ?? null,
                ]
            );
        }
    }
}
