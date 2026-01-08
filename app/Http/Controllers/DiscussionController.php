<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Discussion;
use App\Models\Comment;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    /**
     * Get or create discussion for a bill section
     */
    public function getOrCreate(Bill $bill, string $section)
    {
        $discussion = Discussion::firstOrCreate(
            [
                'bill_id' => $bill->id,
                'section' => $section,
            ],
            [
                'comment_count' => 0,
            ]
        );

        // Redirect back to the bill page to reload with new discussion data
        return redirect()->route('bills.show', $bill->id);
    }

    /**
     * Store a new comment
     */
    public function storeComment(Request $request, Bill $bill, Discussion $discussion)
    {
        $user = $request->user();

        // Check if user has accepted guidelines (first-time submission)
        if (!$user->hasAcceptedGuidelines()) {
            $request->validate([
                'accept_guidelines' => 'required|accepted',
            ], [
                'accept_guidelines.required' => 'You must accept the Community Guidelines to post a comment.',
                'accept_guidelines.accepted' => 'You must accept the Community Guidelines to post a comment.',
            ]);

            $user->acceptGuidelines();
        }

        $validated = $request->validate([
            'content' => 'required|string|min:10|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // Create comment
        $comment = Comment::create([
            'discussion_id' => $discussion->id,
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'bill_version_id' => $bill->latestVersion()?->id,
            'depth' => $validated['parent_id']
                ? Comment::find($validated['parent_id'])->depth + 1
                : 0,
        ]);

        // Update discussion metadata
        $discussion->incrementCommentCount();

        // Calculate nested set values
        $this->calculateNestedSet($comment);

        return back()->with('success', 'Comment posted successfully.');
    }

    /**
     * Mark comment as helpful
     */
    public function markHelpful(Request $request, Comment $comment)
    {
        $comment->markHelpful($request->user());

        return back();
    }

    /**
     * Flag comment for moderation
     */
    public function flagComment(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $comment->flag();

        return back()->with('success', 'Comment flagged for review.');
    }

    /**
     * Delete a comment (soft delete)
     */
    public function destroyComment(Request $request, Comment $comment)
    {
        // Only author or admin can delete
        if ($comment->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $discussion = $comment->discussion;
        $comment->delete();

        // Update discussion count
        $discussion->decrementCommentCount();

        return back()->with('success', 'Comment deleted.');
    }

    /**
     * Calculate nested set values for a comment
     */
    private function calculateNestedSet(Comment $comment)
    {
        if ($comment->parent_id) {
            $parent = Comment::find($comment->parent_id);

            // Shift existing nodes to make room
            Comment::where('discussion_id', $comment->discussion_id)
                ->where('rgt', '>=', $parent->rgt)
                ->increment('rgt', 2);

            Comment::where('discussion_id', $comment->discussion_id)
                ->where('lft', '>', $parent->rgt)
                ->increment('lft', 2);

            // Set new comment's lft and rgt
            $comment->lft = $parent->rgt;
            $comment->rgt = $parent->rgt + 1;
        } else {
            // Top-level comment
            $maxRgt = Comment::where('discussion_id', $comment->discussion_id)
                ->max('rgt') ?? 0;

            $comment->lft = $maxRgt + 1;
            $comment->rgt = $maxRgt + 2;
        }

        $comment->save();
    }
}
