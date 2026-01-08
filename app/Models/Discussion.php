<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'section',
        'is_pinned',
        'is_locked',
        'comment_count',
        'last_activity_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'comment_count' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the bill this discussion belongs to
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get all comments for this discussion
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get only top-level comments (no parent)
     */
    public function topLevelComments()
    {
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get human-readable section name
     */
    public function getSectionLabelAttribute()
    {
        return match($this->section) {
            'key_questions' => 'Key Questions',
            'arguments_for' => 'Arguments For',
            'arguments_against' => 'Arguments Against',
            'impact_analysis' => 'Impact Analysis',
            'general' => 'General Discussion',
            default => ucwords(str_replace('_', ' ', $this->section)),
        };
    }

    /**
     * Increment comment count
     */
    public function incrementCommentCount()
    {
        $this->increment('comment_count');
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Decrement comment count
     */
    public function decrementCommentCount()
    {
        $this->decrement('comment_count');
    }
}
