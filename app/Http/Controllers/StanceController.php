<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\UserStance;
use Illuminate\Http\Request;

class StanceController extends Controller
{
    /**
     * Store a new stance or update existing one
     */
    public function store(Request $request, Bill $bill)
    {
        $user = $request->user();

        // Check if user has accepted guidelines (first-time submission)
        if (!$user->hasAcceptedGuidelines()) {
            $request->validate([
                'accept_guidelines' => 'required|accepted',
            ], [
                'accept_guidelines.required' => 'You must accept the Community Guidelines to submit a stance.',
                'accept_guidelines.accepted' => 'You must accept the Community Guidelines to submit a stance.',
            ]);

            $user->acceptGuidelines();
        }

        $validated = $request->validate([
            'stance' => 'required|in:support,oppose,mixed,undecided,needs_more_info',
            'reason' => 'required|string|min:50|max:5000',
        ], [
            'reason.min' => 'Please provide a substantive reason (at least 50 characters).',
            'reason.max' => 'Reason must not exceed 5000 characters.',
        ]);

        // Get existing stance if any
        $existingStance = UserStance::where('user_id', $user->id)
            ->where('bill_id', $bill->id)
            ->first();

        if ($existingStance) {
            // Soft delete the old stance (preserves history)
            $existingStance->delete();

            $newRevision = $existingStance->revision + 1;
            $previousStanceId = $existingStance->id;
        } else {
            $newRevision = 1;
            $previousStanceId = null;
        }

        // Create new stance with ZIP code snapshot
        $stance = UserStance::create([
            'user_id' => $user->id,
            'bill_id' => $bill->id,
            'stance' => $validated['stance'],
            'reason' => $validated['reason'],
            'zip_code' => $user->zip_code,
            'congressional_district' => $user->congressional_district,
            'revision' => $newRevision,
            'previous_stance_id' => $previousStanceId,
            'bill_version_id' => $bill->latestVersion()?->id,
        ]);

        return back()->with('success', 'Your stance has been recorded.');
    }

    /**
     * Delete a stance
     */
    public function destroy(Request $request, Bill $bill)
    {
        $stance = UserStance::where('user_id', $request->user()->id)
            ->where('bill_id', $bill->id)
            ->first();

        if ($stance) {
            $stance->delete();
            return back()->with('success', 'Your stance has been removed.');
        }

        return back()->with('error', 'Stance not found.');
    }
}
