<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'congress_number',
        'chamber',
        'bill_type',
        'bill_number',
        'title',
        'short_title',
        'summary',
        'constitutional_authority_statement',
        'committees',
        'subjects',
        'policy_area',
        'status',
        'introduced_date',
        'last_action_at',
        'last_action_text',
        'affected_states',
        'affected_districts',
        'is_national',
        'congress_gov_url',
        'govtrack_url',
        'last_synced_at',
        'sync_source',
        'confidence_score',
    ];

    protected $appends = [
        'identifier',
        'congress_gov_url',
        'status_display',
    ];

    protected function casts(): array
    {
        return [
            'introduced_date' => 'date',
            'last_action_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'affected_states' => 'array',
            'affected_districts' => 'array',
            'committees' => 'array',
            'subjects' => 'array',
            'is_national' => 'boolean',
        ];
    }

    /**
     * Get all versions of this bill.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(BillVersion::class)->orderBy('published_at', 'desc');
    }

    /**
     * Get all events for this bill.
     */
    public function events(): HasMany
    {
        return $this->hasMany(BillEvent::class)->orderBy('occurred_at', 'desc');
    }

    /**
     * Get all actors (sponsors, cosponsors, committees) for this bill.
     */
    public function actors(): HasMany
    {
        return $this->hasMany(BillActor::class);
    }

    /**
     * Get the primary sponsor.
     */
    public function sponsor()
    {
        return $this->actors()->where('actor_type', 'sponsor')->where('is_primary', true)->first();
    }

    /**
     * Get all cosponsors.
     */
    public function cosponsors(): HasMany
    {
        return $this->actors()->where('actor_type', 'cosponsor');
    }

    /**
     * Get all committees.
     */
    public function committees(): HasMany
    {
        return $this->actors()->where('actor_type', 'committee');
    }

    /**
     * Get the latest version of the bill text.
     */
    public function latestVersion()
    {
        return $this->versions()->first();
    }

    /**
     * Generate bill identifier (e.g., "H.R. 1234" or "S. 567").
     */
    public function getIdentifierAttribute(): string
    {
        $billType = strtolower($this->bill_type);

        $prefix = match($billType) {
            'hr' => 'H.R.',
            's' => 'S.',
            'hjres' => 'H.J.Res.',
            'sjres' => 'S.J.Res.',
            'hconres' => 'H.Con.Res.',
            'sconres' => 'S.Con.Res.',
            'hres' => 'H.Res.',
            'sres' => 'S.Res.',
            default => strtoupper($this->bill_type),
        };

        return "{$prefix} {$this->bill_number}";
    }

    /**
     * Get the user-facing Congress.gov URL (not the API URL).
     */
    public function getCongressGovUrlAttribute(): ?string
    {
        // Convert API URL to user-facing URL
        // API format: https://api.congress.gov/v3/bill/119/s/3581
        // User format: https://www.congress.gov/bill/119th-congress/senate-bill/3581

        if (!$this->attributes['congress_gov_url']) {
            return null;
        }

        $apiUrl = $this->attributes['congress_gov_url'];

        // If it's already a user-facing URL, return it
        if (str_contains($apiUrl, 'www.congress.gov')) {
            return $apiUrl;
        }

        // Convert API URL to user-facing URL
        $chamberMap = [
            'house' => 'house-bill',
            'senate' => 'senate-bill',
        ];

        $billTypeMap = [
            'hr' => 'house-bill',
            's' => 'senate-bill',
            'hjres' => 'house-joint-resolution',
            'sjres' => 'senate-joint-resolution',
            'hconres' => 'house-concurrent-resolution',
            'sconres' => 'senate-concurrent-resolution',
            'hres' => 'house-resolution',
            'sres' => 'senate-resolution',
        ];

        $billType = $billTypeMap[$this->bill_type] ?? $chamberMap[$this->chamber] ?? 'bill';

        return sprintf(
            'https://www.congress.gov/bill/%sth-congress/%s/%s',
            $this->congress_number,
            $billType,
            $this->bill_number
        );
    }

    /**
     * Get formatted status display text.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'introduced' => 'Introduced',
            'in_committee' => 'In Committee',
            'on_floor' => 'On Floor',
            'passed_chamber' => 'Passed Chamber',
            'passed_both' => 'Passed Both Chambers',
            'to_president' => 'To President',
            'became_law' => 'Became Law',
            'failed' => 'Failed',
            'vetoed' => 'Vetoed',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Check if bill data is stale (needs re-sync).
     */
    public function isStale(int $hoursThreshold = 24): bool
    {
        if (!$this->last_synced_at) {
            return true;
        }

        return $this->last_synced_at->diffInHours(now()) > $hoursThreshold;
    }

    /**
     * Check if bill is active (not passed or failed).
     */
    public function isActive(): bool
    {
        return !in_array($this->status, ['became_law', 'failed', 'vetoed']);
    }

    /**
     * Determine if bill affects a specific state or district.
     * Note: National bills don't automatically "affect" a location - they affect everyone equally.
     * This method checks for bills with specific geographic impact.
     */
    public function affectsLocation(string $state, ?string $district = null): bool
    {
        // National bills affect all locations equally, so they don't have specific local relevance
        if ($this->is_national) {
            return false;
        }

        if ($this->affected_states && in_array($state, $this->affected_states)) {
            return true;
        }

        if ($district && $this->affected_districts) {
            $fullDistrict = "{$state}-{$district}";
            return in_array($fullDistrict, $this->affected_districts);
        }

        return false;
    }

    /**
     * Get all users following this bill.
     */
    public function followers(): HasMany
    {
        return $this->hasMany(BillFollower::class);
    }

    /**
     * Get all user stances on this bill.
     */
    public function stances(): HasMany
    {
        return $this->hasMany(UserStance::class);
    }

    /**
     * Get all discussions about this bill.
     */
    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }
}
