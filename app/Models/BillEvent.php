<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'event_type',
        'description',
        'occurred_at',
        'chamber',
        'committee_name',
        'vote_details',
        'source',
        'detected_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'detected_at' => 'datetime',
            'vote_details' => 'array',
            'raw_payload' => 'array',
        ];
    }

    /**
     * Get the bill this event belongs to.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Check if this event represents a significant change.
     */
    public function isSignificant(): bool
    {
        return in_array($this->event_type, [
            'introduced',
            'passed_chamber',
            'amended',
            'sent_to_president',
            'signed_by_president',
            'vetoed',
            'became_law',
        ]);
    }
}
