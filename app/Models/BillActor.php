<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillActor extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'actor_type',
        'bioguide_id',
        'name',
        'party',
        'state',
        'district',
        'committee_code',
        'committee_name',
        'joined_at',
        'is_primary',
        'is_original',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'is_primary' => 'boolean',
            'is_original' => 'boolean',
        ];
    }

    /**
     * Get the bill this actor is associated with.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get formatted actor name with party/state.
     */
    public function getFormattedNameAttribute(): string
    {
        if ($this->actor_type === 'committee') {
            return $this->committee_name;
        }

        $parts = [$this->name];

        if ($this->party) {
            $parts[] = $this->party;
        }

        if ($this->state) {
            $district = $this->district ? "-{$this->district}" : '';
            $parts[] = "{$this->state}{$district}";
        }

        return implode(' ', $parts);
    }
}
