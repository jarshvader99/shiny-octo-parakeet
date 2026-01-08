<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'discussion_id',
        'user_id',
        'parent_id',
        'content',
        'bill_version_id',
        'is_flagged',
        'flag_count',
        'is_hidden',
        'hidden_reason',
        'helpful_count',
        'lft',
        'rgt',
        'depth',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'is_hidden' => 'boolean',
        'flag_count' => 'integer',
        'helpful_count' => 'integer',
        'depth' => 'integer',
    ];

    /**
     * Get the discussion this comment belongs to
     */
    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    /**
     * Get the user who posted this comment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all replies to this comment
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get the bill version this comment was made against
     */
    public function billVersion()
    {
        return $this->belongsTo(BillVersion::class);
    }

    /**
     * Get users who marked this comment as helpful
     */
    public function helpfulVotes()
    {
        return $this->belongsToMany(User::class, 'comment_helpful_votes')
            ->withTimestamps();
    }

    /**
     * Check if bill has been amended since this comment
     */
    public function isBillOutdated()
    {
        if (!$this->bill_version_id) {
            return false;
        }

        $bill = $this->discussion->bill;
        $latestVersion = $bill->latestVersion();

        return $latestVersion && $latestVersion->id !== $this->bill_version_id;
    }

    /**
     * Mark comment as helpful by a user
     */
    public function markHelpful(User $user)
    {
        // Check if user already voted
        if ($this->hasUserMarkedHelpful($user)) {
            return false;
        }

        // Add helpful vote
        $this->helpfulVotes()->attach($user->id);
        $this->increment('helpful_count');

        return true;
    }

    /**
     * Check if a user has already marked this comment as helpful
     */
    public function hasUserMarkedHelpful(User $user)
    {
        return $this->helpfulVotes()->where('user_id', $user->id)->exists();
    }

    /**
     * Flag comment for moderation
     */
    public function flag()
    {
        $this->increment('flag_count');

        // Auto-flag if threshold reached
        if ($this->flag_count >= 5 && !$this->is_flagged) {
            $this->update(['is_flagged' => true]);
        }
    }

    /**
     * Get all descendants (nested replies)
     */
    public function descendants()
    {
        return Comment::where('discussion_id', $this->discussion_id)
            ->where('lft', '>', $this->lft)
            ->where('rgt', '<', $this->rgt)
            ->orderBy('lft');
    }
}
