<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserStance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'bill_id',
        'stance',
        'reason',
        'zip_code',
        'congressional_district',
        'revision',
        'previous_stance_id',
        'bill_version_id',
    ];

    protected $casts = [
        'revision' => 'integer',
    ];

    /**
     * Get the user who submitted this stance
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bill this stance is for
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the bill version this stance was made against
     */
    public function billVersion()
    {
        return $this->belongsTo(BillVersion::class);
    }

    /**
     * Get the previous stance revision
     */
    public function previousStance()
    {
        return $this->belongsTo(UserStance::class, 'previous_stance_id');
    }

    /**
     * Get all revision history for this stance
     */
    public function revisionHistory()
    {
        $history = collect([$this]);
        $current = $this;

        while ($current->previousStance) {
            $current = $current->previousStance;
            $history->push($current);
        }

        return $history;
    }

    /**
     * Check if bill has been amended since this stance
     */
    public function isBillOutdated()
    {
        if (!$this->bill_version_id) {
            return false;
        }

        $latestVersion = $this->bill->latestVersion();

        return $latestVersion && $latestVersion->id !== $this->bill_version_id;
    }

    /**
     * Get human-readable stance label
     */
    public function getStanceLabelAttribute()
    {
        return match($this->stance) {
            'support' => 'Support',
            'oppose' => 'Oppose',
            'mixed' => 'Mixed Feelings',
            'undecided' => 'Undecided',
            'needs_more_info' => 'Need More Info',
            default => ucfirst($this->stance),
        };
    }
}
