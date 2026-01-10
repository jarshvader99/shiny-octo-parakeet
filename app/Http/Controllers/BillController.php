<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Discussion;
use App\Models\UserStance;
use App\Services\ConsensusMetricsService;
use App\Services\GeographicConsensusService;
use App\Services\LocalBillService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillController extends Controller
{
    public function __construct(
        private LocalBillService $localBillService,
        private ConsensusMetricsService $consensusMetricsService,
        private GeographicConsensusService $geographicConsensusService
    ) {}

    /**
     * Display a listing of all bills with search/filter.
     */
    public function index(Request $request)
    {
        $bills = $this->localBillService->searchBills(
            keyword: $request->input('search'),
            status: $request->input('status'),
            chamber: $request->input('chamber'),
            sponsor: $request->input('sponsor'),
            sort: $request->input('sort', 'last_action'),
            perPage: 20
        );

        // Transform the bills while preserving pagination
        $bills->through(function ($bill) use ($request) {
            return [
                'id' => $bill->id,
                'identifier' => $bill->identifier,
                'title' => $bill->title,
                'summary' => $bill->summary,
                'status' => $bill->status,
                'status_display' => ucwords(str_replace('_', ' ', $bill->status)),
                'chamber' => $bill->chamber,
                'last_action_at' => $bill->last_action_at,
                'stances_count' => $bill->stances_count,
                'followers_count' => $bill->followers_count,
                'comments_count' => $bill->comments_count,
                'is_locally_relevant' => $request->user() ? $this->localBillService->isLocallyRelevant($bill, $request->user()) : false,
                'sponsor' => $bill->sponsor() ? [
                    'name' => $bill->sponsor()->name,
                    'party' => $bill->sponsor()->party,
                ] : null,
            ];
        });

        return Inertia::render('Bills/Index', [
            'bills' => $bills,
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'chamber' => $request->input('chamber'),
                'sponsor' => $request->input('sponsor'),
                'sort' => $request->input('sort', 'last_action'),
            ],
        ]);
    }

    /**
     * Display the specified bill.
     */
    public function show(Request $request, Bill $bill)
    {
        // Eager load all relationships to avoid N+1 queries
        $bill->load([
            'versions' => fn($query) => $query->latest()->limit(1),
            'events' => fn($query) => $query->orderBy('occurred_at', 'desc')->limit(10),
            'actors',
            'followers',
            'stances' => fn($query) => $query->with('user:id,name'),
            'discussions.comments' => fn($query) => $query->with('user:id,name')
                ->withCount('helpfulVotes')
                ->latest()
                ->limit(50),
        ]);

        $isLocallyRelevant = $this->localBillService->isLocallyRelevant(
            $bill,
            $request->user()
        );

        return Inertia::render('Bills/Show', [
            'bill' => [
                'id' => $bill->id,
                'identifier' => $bill->identifier,
                'congress_number' => $bill->congress_number,
                'title' => $bill->title,
                'short_title' => $bill->short_title,
                'summary' => $bill->summary,
                'status' => $bill->status,
                'status_display' => $bill->getStatusDisplayAttribute(),
                'chamber' => $bill->chamber,
                'introduced_at' => $bill->introduced_date->format('M d, Y'),
                'last_action_at' => $bill->last_action_at?->format('M d, Y'),
                'last_action_text' => $bill->last_action_text,
                'last_synced_at' => $bill->last_synced_at?->format('M d, Y'),
                'congress_gov_url' => $bill->getCongressGovUrlAttribute(),
                'is_stale' => $bill->isStale(),
                'is_active' => $bill->isActive(),
                'is_locally_relevant' => $isLocallyRelevant,
                'sponsor' => $bill->sponsor() ? [
                    'name' => $bill->sponsor()->name,
                    'party' => $bill->sponsor()->party,
                    'state' => $bill->sponsor()->state,
                    'district' => $bill->sponsor()->district,
                ] : null,
                'cosponsors' => $bill->cosponsors()->take(10)->get()->map(fn($actor) => [
                    'name' => $actor->name,
                    'party' => $actor->party,
                    'state' => $actor->state,
                ]),
                'committees' => $bill->committees ?? [],
                'subjects' => $bill->subjects ?? [],
                'policy_area' => $bill->policy_area,
                'constitutional_authority_statement' => $bill->constitutional_authority_statement,
                'events' => $bill->events->map(fn($event) => [
                    'type' => $event->event_type,
                    'description' => $event->description,
                    'occurred_at' => $event->occurred_at->format('M d, Y'),
                    'is_significant' => $event->isSignificant(),
                ])->take(10),
                'latest_version' => $bill->latestVersion() ? [
                    'version_name' => $bill->latestVersion()->version_name,
                    'published_at' => $bill->latestVersion()->published_at->format('M d, Y'),
                    'text_url' => $bill->latestVersion()->text_url,
                    'pdf_url' => $bill->latestVersion()->pdf_url,
                ] : null,
                'user_stance' => $this->getUserStance($bill, $request->user()),
                'consensus' => $this->getConsensusData($bill),
                'consensus_metrics' => $this->consensusMetricsService->calculateMetrics($bill),
                'geographic_data' => $this->geographicConsensusService->getStateConsensus($bill),
                'discussions' => $this->getDiscussions($bill),
                'is_following' => $request->user()->isFollowing($bill),
                'follower_count' => $bill->followers()->count(),
            ],
        ]);
    }

    /**
     * Get user's current stance on the bill
     */
    private function getUserStance(Bill $bill, $user)
    {
        if (!$user) {
            return null;
        }

        $stance = UserStance::where('user_id', $user->id)
            ->where('bill_id', $bill->id)
            ->first();

        if (!$stance) {
            return null;
        }

        return [
            'id' => $stance->id,
            'stance' => $stance->stance,
            'stance_label' => $stance->stance_label,
            'reason' => $stance->reason,
            'revision' => $stance->revision,
            'created_at' => $stance->created_at->toISOString(),
            'updated_at' => $stance->updated_at->toISOString(),
            'congressional_district' => $stance->congressional_district,
            'is_outdated' => $stance->isBillOutdated(),
        ];
    }

    /**
     * Get aggregate consensus data for the bill
     */
    private function getConsensusData(Bill $bill)
    {
        $stances = UserStance::where('bill_id', $bill->id)->get();
        $total = $stances->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'breakdown' => [],
                'percentages' => [],
            ];
        }

        $breakdown = [
            'support' => $stances->where('stance', 'support')->count(),
            'oppose' => $stances->where('stance', 'oppose')->count(),
            'mixed' => $stances->where('stance', 'mixed')->count(),
            'undecided' => $stances->where('stance', 'undecided')->count(),
            'needs_more_info' => $stances->where('stance', 'needs_more_info')->count(),
        ];

        $percentages = [
            'support' => round(($breakdown['support'] / $total) * 100, 1),
            'oppose' => round(($breakdown['oppose'] / $total) * 100, 1),
            'mixed' => round(($breakdown['mixed'] / $total) * 100, 1),
            'undecided' => round(($breakdown['undecided'] / $total) * 100, 1),
            'needs_more_info' => round(($breakdown['needs_more_info'] / $total) * 100, 1),
        ];

        return [
            'total' => $total,
            'breakdown' => $breakdown,
            'percentages' => $percentages,
        ];
    }

    /**
     * Get discussions for all sections
     */
    private function getDiscussions(Bill $bill)
    {
        $sections = ['key_questions', 'arguments_for', 'arguments_against', 'impact_analysis', 'general'];
        $discussions = [];

        foreach ($sections as $section) {
            $discussion = Discussion::where('bill_id', $bill->id)
                ->where('section', $section)
                ->first();

            if ($discussion) {
                $discussions[$section] = [
                    'id' => $discussion->id,
                    'section' => $discussion->section,
                    'section_label' => $discussion->section_label,
                    'comment_count' => $discussion->comment_count,
                    'is_locked' => $discussion->is_locked,
                    'comments' => $discussion->topLevelComments->load('user', 'replies.user')->map(fn($comment) => [
                        'id' => $comment->id,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                        'content' => $comment->content,
                        'helpful_count' => $comment->helpful_count,
                        'is_outdated' => $comment->isBillOutdated(),
                        'created_at' => $comment->created_at->toISOString(),
                        'updated_at' => $comment->updated_at->toISOString(),
                        'replies' => $comment->replies->map(fn($reply) => [
                            'id' => $reply->id,
                            'user' => [
                                'id' => $reply->user->id,
                                'name' => $reply->user->name,
                            ],
                            'content' => $reply->content,
                            'helpful_count' => $reply->helpful_count,
                            'created_at' => $reply->created_at->toISOString(),
                            'updated_at' => $reply->updated_at->toISOString(),
                        ]),
                    ]),
                ];
            } else {
                // Create placeholder for sections without discussions yet
                $discussions[$section] = null;
            }
        }

        return $discussions;
    }
}
