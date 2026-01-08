<?php

namespace App\Jobs;

use App\Models\Bill;
use App\Services\CongressApiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ResyncStaleBillsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?int $limit = 50,
        public ?int $hoursStale = 72
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * Re-syncs bills that:
     * 1. Haven't been synced in X hours (configurable, default 72)
     * 2. Are missing summaries (to pick up newly published summaries)
     * 3. Are active bills (introduced, in committee, etc.)
     */
    public function handle(CongressApiClient $api): void
    {
        Log::info('Starting stale bill re-sync', [
            'limit' => $this->limit,
            'hours_stale' => $this->hoursStale,
        ]);

        // Find bills that need re-syncing
        $staleBills = Bill::query()
            ->where(function ($query) {
                // Bills without summaries
                $query->whereNull('summary')
                      ->orWhere('summary', '');
            })
            ->orWhere(function ($query) {
                // Or bills that haven't been synced recently
                $query->where('last_synced_at', '<', now()->subHours($this->hoursStale));
            })
            ->where('status', '!=', 'became_law') // Don't re-sync laws (they're final)
            ->where('status', '!=', 'failed') // Don't re-sync failed bills
            ->orderBy('last_synced_at', 'asc') // Oldest first
            ->limit($this->limit)
            ->get();

        if ($staleBills->isEmpty()) {
            Log::info('No stale bills to re-sync');
            return;
        }

        $syncedCount = 0;
        $updatedCount = 0;

        foreach ($staleBills as $bill) {
            try {
                $billType = strtolower($bill->bill_type);

                // Get fresh bill details
                $detailedBill = $api->getBill($bill->congress_number, $billType, $bill->bill_number);

                if (!$detailedBill) {
                    Log::warning('Could not fetch bill details for re-sync', [
                        'bill_id' => $bill->id,
                        'identifier' => $bill->identifier,
                    ]);
                    continue;
                }

                $hasChanges = false;

                // Update summary if it's new or changed
                $summaries = $api->getBillSummaries($bill->congress_number, $billType, $bill->bill_number);
                if (!empty($summaries)) {
                    $latestSummary = $summaries[0] ?? null;
                    $newSummary = $latestSummary['text'] ?? null;

                    if ($newSummary && $newSummary !== $bill->summary) {
                        $bill->summary = $newSummary;
                        $hasChanges = true;
                        Log::info('Updated bill summary', [
                            'bill_id' => $bill->id,
                            'identifier' => $bill->identifier,
                        ]);
                    }
                }

                // Update short title if available
                if (isset($detailedBill['titles']) && is_array($detailedBill['titles'])) {
                    foreach ($detailedBill['titles'] as $titleObj) {
                        if (isset($titleObj['titleType']) &&
                            ($titleObj['titleType'] === 'Short Title(s) as Introduced' ||
                             $titleObj['titleType'] === 'Short Title(s)')) {
                            $newShortTitle = $titleObj['title'];
                            if ($newShortTitle && $newShortTitle !== $bill->short_title) {
                                $bill->short_title = $newShortTitle;
                                $hasChanges = true;
                            }
                            break;
                        }
                    }
                }

                // Update status and last action
                $newStatus = $this->mapBillStatus($detailedBill);
                if ($newStatus !== $bill->status) {
                    $bill->status = $newStatus;
                    $hasChanges = true;
                }

                if (isset($detailedBill['latestAction']['actionDate'])) {
                    $bill->last_action_at = $detailedBill['latestAction']['actionDate'];
                    $bill->last_action_text = $detailedBill['latestAction']['text'] ?? null;
                    $hasChanges = true;
                }

                // Always update sync timestamp
                $bill->last_synced_at = now();
                $bill->save();

                $syncedCount++;
                if ($hasChanges) {
                    $updatedCount++;
                }

            } catch (\Exception $e) {
                Log::error('Error re-syncing bill', [
                    'bill_id' => $bill->id,
                    'identifier' => $bill->identifier,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Stale bill re-sync completed', [
            'checked' => $staleBills->count(),
            'synced' => $syncedCount,
            'updated' => $updatedCount,
        ]);
    }

    /**
     * Map Congress.gov status to our status enum
     */
    private function mapBillStatus(array $bill): string
    {
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
}
