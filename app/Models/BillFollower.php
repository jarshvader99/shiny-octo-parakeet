<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillFollower extends Model
{
    protected $fillable = [
        'user_id',
        'bill_id',
        'notify_on_amendment',
        'notify_on_vote',
        'notify_on_status_change',
        'notify_on_new_discussion',
        'followed_at',
        'last_notified_at',
    ];

    protected $casts = [
        'notify_on_amendment' => 'boolean',
        'notify_on_vote' => 'boolean',
        'notify_on_status_change' => 'boolean',
        'notify_on_new_discussion' => 'boolean',
        'followed_at' => 'datetime',
        'last_notified_at' => 'datetime',
    ];

    /**
     * Get the user who is following
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bill being followed
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Check if enough time has passed since last notification (prevent spam)
     */
    public function canSendNotification(): bool
    {
        if (!$this->last_notified_at) {
            return true;
        }

        // Don't send more than one notification per hour
        return $this->last_notified_at->diffInHours(now()) >= 1;
    }

    /**
     * Mark notification as sent
     */
    public function markNotificationSent(): void
    {
        $this->update(['last_notified_at' => now()]);
    }
}
