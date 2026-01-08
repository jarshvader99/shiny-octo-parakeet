<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillFollowerController extends Controller
{
    /**
     * Display all bills the user is following
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $followedBills = $user->followedBills()
            ->with(['bill' => function($query) {
                $query->with(['actors' => fn($q) => $q->where('is_primary', true)])
                    ->withCount('stances', 'followers');
            }])
            ->latest()
            ->get()
            ->map(function($follower) {
                $bill = $follower->bill;
                return [
                    'id' => $bill->id,
                    'identifier' => $bill->identifier,
                    'title' => $bill->title,
                    'summary' => $bill->summary,
                    'status' => $bill->status,
                    'status_display' => $bill->status_display,
                    'chamber' => $bill->chamber,
                    'introduced_at' => $bill->introduced_date?->format('M d, Y'),
                    'last_action_at' => $bill->last_action_at?->format('M d, Y'),
                    'last_action_text' => $bill->last_action_text,
                    'congress_gov_url' => $bill->congress_gov_url,
                    'sponsor' => $bill->sponsor() ? [
                        'name' => $bill->sponsor()->name,
                        'party' => $bill->sponsor()->party,
                        'state' => $bill->sponsor()->state,
                    ] : null,
                    'stances_count' => $bill->stances_count,
                    'followers_count' => $bill->followers_count,
                    'followed_at' => $follower->created_at->diffForHumans(),
                    'notification_preferences' => [
                        'notify_on_amendment' => $follower->notify_on_amendment,
                        'notify_on_vote' => $follower->notify_on_vote,
                        'notify_on_status_change' => $follower->notify_on_status_change,
                        'notify_on_new_discussion' => $follower->notify_on_new_discussion,
                    ],
                ];
            });

        return Inertia::render('Bills/Following', [
            'followedBills' => $followedBills,
        ]);
    }

    /**
     * Follow a bill
     */
    public function store(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'notify_on_amendment' => 'sometimes|boolean',
            'notify_on_vote' => 'sometimes|boolean',
            'notify_on_status_change' => 'sometimes|boolean',
            'notify_on_new_discussion' => 'sometimes|boolean',
        ]);

        $request->user()->follow($bill, $validated);

        return back()->with('success', 'You are now following this bill.');
    }

    /**
     * Unfollow a bill
     */
    public function destroy(Request $request, Bill $bill)
    {
        $request->user()->unfollow($bill);

        return back()->with('success', 'You have unfollowed this bill.');
    }

    /**
     * Update notification preferences for a followed bill
     */
    public function update(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'notify_on_amendment' => 'required|boolean',
            'notify_on_vote' => 'required|boolean',
            'notify_on_status_change' => 'required|boolean',
            'notify_on_new_discussion' => 'required|boolean',
        ]);

        $follower = $request->user()->followedBills()->where('bill_id', $bill->id)->firstOrFail();
        $follower->update($validated);

        return back()->with('success', 'Notification preferences updated.');
    }
}
