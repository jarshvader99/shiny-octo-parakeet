<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LocalBillService
{
    /**
     * Get bills relevant to a user based on their location.
     */
    public function getBillsForUser(User $user, int $limit = 10): Collection
    {
        if (!$user->hasCompletedProfile()) {
            return collect();
        }

        // Parse state from congressional district (e.g., "CA-12" -> "CA")
        $state = $this->parseStateFromDistrict($user->congressional_district);

        return Bill::query()
            ->where(function (Builder $query) use ($state, $user) {
                // Bills affecting user's state/district
                $query->where(function (Builder $q) use ($state, $user) {
                    $q->where('is_national', false)
                        ->where(function (Builder $sq) use ($state, $user) {
                            // Check affected_states array
                            $sq->whereJsonContains('affected_states', $state)
                                // Check affected_districts array
                                ->orWhereJsonContains('affected_districts', $user->congressional_district);
                        });
                })
                // OR bills from user's representatives
                ->orWhereHas('actors', function (Builder $q) use ($state, $user) {
                    $q->where('actor_type', 'sponsor')
                        ->where('is_primary', true)
                        ->where(function (Builder $sq) use ($state, $user) {
                            $sq->where('state', $state);

                            // If user has a district, match it
                            if ($district = $this->parseDistrictNumber($user->congressional_district)) {
                                $sq->where('district', $district);
                            }
                        });
                });
            })
            ->with(['actors' => fn($q) => $q->where('is_primary', true), 'events' => fn($q) => $q->latest()->limit(3)])
            ->withCount('stances', 'followers', 'comments')
            ->orderBy('last_action_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get national bills (not local).
     */
    public function getNationalBills(int $limit = 20): Collection
    {
        return Bill::query()
            ->where('is_national', true)
            ->with(['actors' => fn($q) => $q->where('is_primary', true), 'events' => fn($q) => $q->latest()->limit(3)])
            ->withCount('stances', 'followers', 'comments')
            ->orderBy('last_action_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all bills with optional filters.
     */
    public function searchBills(
        ?string $keyword = null,
        ?string $status = null,
        ?string $chamber = null,
        ?string $sponsor = null,
        ?string $sort = 'last_action',
        int $perPage = 20
    ) {
        $query = Bill::query()
            ->with(['actors' => fn($q) => $q->where('is_primary', true), 'events' => fn($q) => $q->latest()->limit(3)])
            ->withCount('stances', 'followers', 'comments');

        if ($keyword) {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%")
                    ->orWhere('short_title', 'like', "%{$keyword}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($chamber) {
            $query->where('chamber', $chamber);
        }

        if ($sponsor) {
            $query->whereHas('actors', function (Builder $q) use ($sponsor) {
                $q->where('actor_type', 'sponsor')
                    ->where('name', 'like', "%{$sponsor}%");
            });
        }

        // Apply sorting
        match($sort) {
            'last_action' => $query->orderBy('last_action_at', 'desc')->orderBy('id', 'desc'),
            'last_action_asc' => $query->orderBy('last_action_at', 'asc')->orderBy('id', 'asc'),
            'introduced' => $query->orderBy('introduced_date', 'desc')->orderBy('id', 'desc'),
            'introduced_asc' => $query->orderBy('introduced_date', 'asc')->orderBy('id', 'asc'),
            'popular' => $query->orderBy('stances_count', 'desc')->orderBy('followers_count', 'desc')->orderBy('id', 'desc'),
            default => $query->orderBy('last_action_at', 'desc')->orderBy('id', 'desc'),
        };

        return $query->paginate($perPage);
    }

    /**
     * Determine if a bill is locally relevant to a user.
     */
    public function isLocallyRelevant(Bill $bill, User $user): bool
    {
        if (!$user->hasCompletedProfile()) {
            return false;
        }

        $state = $this->parseStateFromDistrict($user->congressional_district);
        $district = $this->parseDistrictNumber($user->congressional_district);

        // Check if bill affects user's location
        if ($bill->affectsLocation($state, $district)) {
            return true;
        }

        // Check if sponsored by user's representative
        $sponsor = $bill->sponsor();
        if ($sponsor && $sponsor->state === $state) {
            if (!$district || $sponsor->district === $district) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse state code from congressional district string.
     * Example: "CA-12" -> "CA"
     */
    private function parseStateFromDistrict(?string $district): ?string
    {
        if (!$district || !str_contains($district, '-')) {
            return null;
        }

        return explode('-', $district)[0];
    }

    /**
     * Parse district number from congressional district string.
     * Example: "CA-12" -> "12"
     */
    private function parseDistrictNumber(?string $district): ?string
    {
        if (!$district || !str_contains($district, '-')) {
            return null;
        }

        return explode('-', $district)[1];
    }
}
