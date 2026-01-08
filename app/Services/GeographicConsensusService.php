<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\UserStance;
use Illuminate\Support\Facades\DB;

class GeographicConsensusService
{
    /**
     * Minimum sample size to display regional data (privacy threshold)
     */
    private const MIN_SAMPLE_SIZE = 5;

    /**
     * Get consensus data aggregated by state
     */
    public function getStateConsensus(Bill $bill): array
    {
        $stances = UserStance::where('bill_id', $bill->id)
            ->whereNotNull('zip_code')
            ->get();

        if ($stances->isEmpty()) {
            return [];
        }

        // Group by state (derived from ZIP code)
        $byState = $stances->groupBy(function ($stance) {
            // Extract state from zip_code (first 2 digits map to state)
            // This is simplified - in production, use a proper ZIP->State lookup
            return $this->zipToState($stance->zip_code);
        })->filter(function ($stances) {
            // Only include states with minimum sample size (privacy threshold)
            return $stances->count() >= self::MIN_SAMPLE_SIZE;
        });

        $stateData = [];
        foreach ($byState as $state => $stateStances) {
            if (!$state) continue;

            $total = $stateStances->count();
            $breakdown = [
                'support' => $stateStances->where('stance', 'support')->count(),
                'oppose' => $stateStances->where('stance', 'oppose')->count(),
                'mixed' => $stateStances->where('stance', 'mixed')->count(),
                'undecided' => $stateStances->where('stance', 'undecided')->count(),
                'needs_more_info' => $stateStances->where('stance', 'needs_more_info')->count(),
            ];

            $dominant = collect($breakdown)->sortDesc()->keys()->first();
            $dominantPercentage = $total > 0 ? round(($breakdown[$dominant] / $total) * 100, 1) : 0;

            $stateData[$state] = [
                'state' => $state,
                'total' => $total,
                'breakdown' => $breakdown,
                'dominant_stance' => $dominant,
                'dominant_percentage' => $dominantPercentage,
                'support_percentage' => $total > 0 ? round(($breakdown['support'] / $total) * 100, 1) : 0,
                'oppose_percentage' => $total > 0 ? round(($breakdown['oppose'] / $total) * 100, 1) : 0,
            ];
        }

        return $stateData;
    }

    /**
     * Get consensus data aggregated by congressional district
     */
    public function getDistrictConsensus(Bill $bill): array
    {
        $stances = UserStance::where('bill_id', $bill->id)
            ->join('users', 'user_stances.user_id', '=', 'users.id')
            ->whereNotNull('users.congressional_district')
            ->select('user_stances.*', 'users.congressional_district')
            ->get();

        if ($stances->isEmpty()) {
            return [];
        }

        // Group by congressional district
        $byDistrict = $stances->groupBy('congressional_district')
            ->filter(function ($stances) {
                // Only include districts with minimum sample size
                return $stances->count() >= self::MIN_SAMPLE_SIZE;
            });

        $districtData = [];
        foreach ($byDistrict as $district => $districtStances) {
            if (!$district) continue;

            $total = $districtStances->count();
            $breakdown = [
                'support' => $districtStances->where('stance', 'support')->count(),
                'oppose' => $districtStances->where('stance', 'oppose')->count(),
                'mixed' => $districtStances->where('stance', 'mixed')->count(),
                'undecided' => $districtStances->where('stance', 'undecided')->count(),
                'needs_more_info' => $districtStances->where('stance', 'needs_more_info')->count(),
            ];

            $dominant = collect($breakdown)->sortDesc()->keys()->first();
            $dominantPercentage = $total > 0 ? round(($breakdown[$dominant] / $total) * 100, 1) : 0;

            $districtData[$district] = [
                'district' => $district,
                'total' => $total,
                'breakdown' => $breakdown,
                'dominant_stance' => $dominant,
                'dominant_percentage' => $dominantPercentage,
                'support_percentage' => $total > 0 ? round(($breakdown['support'] / $total) * 100, 1) : 0,
                'oppose_percentage' => $total > 0 ? round(($breakdown['oppose'] / $total) * 100, 1) : 0,
            ];
        }

        return $districtData;
    }

    /**
     * Get ZIP code level aggregation (for display, not individual locations)
     */
    public function getZipCodeSummary(Bill $bill): array
    {
        $stances = UserStance::where('bill_id', $bill->id)
            ->whereNotNull('zip_code')
            ->get();

        if ($stances->isEmpty()) {
            return [
                'unique_zip_codes' => 0,
                'total_responses' => 0,
                'states_represented' => 0,
            ];
        }

        $uniqueZips = $stances->pluck('zip_code')->unique()->count();
        $states = $stances->map(fn($s) => $this->zipToState($s->zip_code))->unique()->filter()->count();

        return [
            'unique_zip_codes' => $uniqueZips,
            'total_responses' => $stances->count(),
            'states_represented' => $states,
        ];
    }

    /**
     * Calculate color intensity for choropleth map
     */
    public function getColorIntensity(array $regionData): float
    {
        // Base intensity on sample size and dominant percentage
        $sampleWeight = min($regionData['total'] / 100, 1.0); // Cap at 100 responses
        $dominanceWeight = $regionData['dominant_percentage'] / 100;

        return round(($sampleWeight + $dominanceWeight) / 2, 2);
    }

    /**
     * Get color based on dominant stance
     */
    public function getStanceColor(string $stance): string
    {
        return match($stance) {
            'support' => '#10b981', // emerald-500
            'oppose' => '#ef4444',  // rose-500
            'mixed' => '#f59e0b',   // amber-500
            'undecided' => '#78716c', // stone-500
            'needs_more_info' => '#6366f1', // indigo-500
            default => '#64748b', // slate-500
        };
    }

    /**
     * Simple ZIP to State mapping (placeholder)
     * In production, use a proper ZIP code database
     */
    private function zipToState(string $zipCode): ?string
    {
        // Simplified mapping based on ZIP code ranges
        // This should be replaced with actual ZIP->State lookup table
        $zip = (int) substr($zipCode, 0, 3);

        return match(true) {
            $zip >= 350 && $zip <= 369 => 'AL',
            $zip >= 995 && $zip <= 999 => 'AK',
            $zip >= 850 && $zip <= 865 => 'AZ',
            $zip >= 716 && $zip <= 729 => 'AR',
            $zip >= 900 && $zip <= 961 => 'CA',
            $zip >= 800 && $zip <= 816 => 'CO',
            $zip >= 60 && $zip <= 69 => 'CT',
            $zip >= 197 && $zip <= 199 => 'DE',
            $zip >= 320 && $zip <= 349 => 'FL',
            $zip >= 300 && $zip <= 319 => 'GA',
            $zip >= 967 && $zip <= 968 => 'HI',
            $zip >= 832 && $zip <= 838 => 'ID',
            $zip >= 600 && $zip <= 629 => 'IL',
            $zip >= 460 && $zip <= 479 => 'IN',
            $zip >= 500 && $zip <= 528 => 'IA',
            $zip >= 660 && $zip <= 679 => 'KS',
            $zip >= 400 && $zip <= 427 => 'KY',
            $zip >= 700 && $zip <= 714 => 'LA',
            $zip >= 39 && $zip <= 49 => 'ME',
            $zip >= 206 && $zip <= 219 => 'MD',
            $zip >= 10 && $zip <= 27 => 'MA',
            $zip >= 480 && $zip <= 499 => 'MI',
            $zip >= 550 && $zip <= 567 => 'MN',
            $zip >= 386 && $zip <= 397 => 'MS',
            $zip >= 630 && $zip <= 658 => 'MO',
            $zip >= 590 && $zip <= 599 => 'MT',
            $zip >= 680 && $zip <= 693 => 'NE',
            $zip >= 889 && $zip <= 898 => 'NV',
            $zip >= 30 && $zip <= 38 => 'NH',
            $zip >= 70 && $zip <= 89 => 'NJ',
            $zip >= 870 && $zip <= 884 => 'NM',
            $zip >= 100 && $zip <= 149 => 'NY',
            $zip >= 270 && $zip <= 289 => 'NC',
            $zip >= 580 && $zip <= 588 => 'ND',
            $zip >= 430 && $zip <= 458 => 'OH',
            $zip >= 730 && $zip <= 749 => 'OK',
            $zip >= 970 && $zip <= 979 => 'OR',
            $zip >= 150 && $zip <= 196 => 'PA',
            $zip >= 28 && $zip <= 29 => 'RI',
            $zip >= 290 && $zip <= 299 => 'SC',
            $zip >= 570 && $zip <= 577 => 'SD',
            $zip >= 370 && $zip <= 385 => 'TN',
            $zip >= 750 && $zip <= 799 => 'TX',
            $zip >= 840 && $zip <= 847 => 'UT',
            $zip >= 50 && $zip <= 59 => 'VT',
            $zip >= 220 && $zip <= 246 => 'VA',
            $zip >= 980 && $zip <= 994 => 'WA',
            $zip >= 247 && $zip <= 268 => 'WV',
            $zip >= 530 && $zip <= 549 => 'WI',
            $zip >= 820 && $zip <= 831 => 'WY',
            default => null,
        };
    }
}
