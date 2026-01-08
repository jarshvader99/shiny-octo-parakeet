<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\UserStance;

class ConsensusMetricsService
{
    /**
     * Calculate comprehensive consensus metrics for a bill
     */
    public function calculateMetrics(Bill $bill): array
    {
        $allStances = UserStance::where('bill_id', $bill->id)
            ->with('user')
            ->get();

        if ($allStances->isEmpty()) {
            return $this->getEmptyMetrics();
        }

        return [
            'raw' => $this->calculateRawMetrics($allStances),
            'engaged' => $this->calculateEngagedMetrics($allStances),
            'trends' => $this->calculateTrends($bill),
            'geographic' => $this->calculateGeographicDistribution($allStances),
            'freshness' => $this->calculateDataFreshness($allStances),
        ];
    }

    /**
     * Calculate raw unweighted metrics
     */
    private function calculateRawMetrics($stances): array
    {
        $total = $stances->count();
        $breakdown = [
            'support' => $stances->where('stance', 'support')->count(),
            'oppose' => $stances->where('stance', 'oppose')->count(),
            'mixed' => $stances->where('stance', 'mixed')->count(),
            'undecided' => $stances->where('stance', 'undecided')->count(),
            'needs_more_info' => $stances->where('stance', 'needs_more_info')->count(),
        ];

        $percentages = [];
        foreach ($breakdown as $stance => $count) {
            $percentages[$stance] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        return [
            'total' => $total,
            'breakdown' => $breakdown,
            'percentages' => $percentages,
            'score' => $this->calculateConsensusScore($percentages),
        ];
    }

    /**
     * Calculate engaged metrics (filters out low-quality stances)
     */
    private function calculateEngagedMetrics($stances): array
    {
        // Filter for engaged users (e.g., reason length > 100 chars)
        $engagedStances = $stances->filter(function ($stance) {
            return strlen($stance->reason) >= 100;
        });

        if ($engagedStances->isEmpty()) {
            return $this->getEmptyMetrics()['raw'];
        }

        $total = $engagedStances->count();
        $breakdown = [
            'support' => $engagedStances->where('stance', 'support')->count(),
            'oppose' => $engagedStances->where('stance', 'oppose')->count(),
            'mixed' => $engagedStances->where('stance', 'mixed')->count(),
            'undecided' => $engagedStances->where('stance', 'undecided')->count(),
            'needs_more_info' => $engagedStances->where('stance', 'needs_more_info')->count(),
        ];

        $percentages = [];
        foreach ($breakdown as $stance => $count) {
            $percentages[$stance] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        return [
            'total' => $total,
            'breakdown' => $breakdown,
            'percentages' => $percentages,
            'score' => $this->calculateConsensusScore($percentages),
        ];
    }

    /**
     * Calculate consensus score (0-100, higher = stronger agreement)
     */
    private function calculateConsensusScore(array $percentages): float
    {
        // Find the dominant position
        $max = max($percentages['support'] ?? 0, $percentages['oppose'] ?? 0);

        // Calculate polarization (how divided opinions are)
        $polarization = abs(($percentages['support'] ?? 0) - ($percentages['oppose'] ?? 0));

        // Penalize uncertainty (mixed, undecided, needs_more_info)
        $uncertainty = ($percentages['mixed'] ?? 0) +
                      ($percentages['undecided'] ?? 0) +
                      ($percentages['needs_more_info'] ?? 0);

        // Score: dominant position strength + polarization - uncertainty penalty
        $score = $max + ($polarization * 0.5) - ($uncertainty * 0.3);

        return round(min(100, max(0, $score)), 1);
    }

    /**
     * Calculate trend data over time
     */
    private function calculateTrends(Bill $bill): array
    {
        $stances = UserStance::where('bill_id', $bill->id)
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($stance) {
                return $stance->created_at->format('Y-m-d');
            });

        $timeline = [];
        $runningTotals = [
            'support' => 0,
            'oppose' => 0,
            'mixed' => 0,
            'undecided' => 0,
            'needs_more_info' => 0,
        ];

        foreach ($stances as $date => $dayStances) {
            foreach ($dayStances as $stance) {
                $runningTotals[$stance->stance]++;
            }

            $total = array_sum($runningTotals);
            $timeline[] = [
                'date' => $date,
                'total' => $total,
                'percentages' => [
                    'support' => $total > 0 ? round(($runningTotals['support'] / $total) * 100, 1) : 0,
                    'oppose' => $total > 0 ? round(($runningTotals['oppose'] / $total) * 100, 1) : 0,
                    'mixed' => $total > 0 ? round(($runningTotals['mixed'] / $total) * 100, 1) : 0,
                    'undecided' => $total > 0 ? round(($runningTotals['undecided'] / $total) * 100, 1) : 0,
                    'needs_more_info' => $total > 0 ? round(($runningTotals['needs_more_info'] / $total) * 100, 1) : 0,
                ],
            ];
        }

        return $timeline;
    }

    /**
     * Calculate geographic distribution summary
     */
    private function calculateGeographicDistribution($stances): array
    {
        $byDistrict = $stances->groupBy('zip_code');

        return [
            'unique_zip_codes' => $byDistrict->count(),
            'coverage' => $this->calculateGeographicCoverage($stances),
        ];
    }

    /**
     * Calculate geographic coverage percentage
     */
    private function calculateGeographicCoverage($stances): float
    {
        // Simplified: assumes 435 congressional districts
        $uniqueZips = $stances->pluck('zip_code')->unique()->count();
        // Rough approximation: ~41,000 ZIP codes in US
        return round(($uniqueZips / 41000) * 100, 2);
    }

    /**
     * Calculate data freshness metrics
     */
    private function calculateDataFreshness($stances): array
    {
        if ($stances->isEmpty()) {
            return [
                'last_stance_at' => null,
                'hours_since_last' => null,
                'is_stale' => true,
            ];
        }

        $lastStance = $stances->sortByDesc('created_at')->first();
        $hoursSince = $lastStance->created_at->diffInHours(now());

        return [
            'last_stance_at' => $lastStance->created_at->toISOString(),
            'hours_since_last' => $hoursSince,
            'is_stale' => $hoursSince > 48, // Stale if no activity in 48 hours
        ];
    }

    /**
     * Get empty metrics structure
     */
    private function getEmptyMetrics(): array
    {
        $emptyBreakdown = [
            'total' => 0,
            'breakdown' => [
                'support' => 0,
                'oppose' => 0,
                'mixed' => 0,
                'undecided' => 0,
                'needs_more_info' => 0,
            ],
            'percentages' => [
                'support' => 0,
                'oppose' => 0,
                'mixed' => 0,
                'undecided' => 0,
                'needs_more_info' => 0,
            ],
            'score' => 0,
        ];

        return [
            'raw' => $emptyBreakdown,
            'engaged' => $emptyBreakdown,
            'trends' => [],
            'geographic' => [
                'unique_zip_codes' => 0,
                'coverage' => 0,
            ],
            'freshness' => [
                'last_stance_at' => null,
                'hours_since_last' => null,
                'is_stale' => true,
            ],
        ];
    }
}
