<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CongressApiClient
{
    protected string $baseUrl = 'https://api.congress.gov/v3';
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.congress.api_key') ?? env('CONGRESS_API_KEY') ?? '';

        if (empty($this->apiKey)) {
            throw new \Exception('Congress API key not configured. Please set CONGRESS_API_KEY in .env');
        }
    }

    /**
     * Get list of bills for a specific congress
     *
     * @param int $congress Congress number (e.g., 118 for 118th Congress)
     * @param int $offset Pagination offset
     * @param int $limit Number of results per page
     * @return array
     */
    public function getBills(int $congress = 118, int $offset = 0, int $limit = 20): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill/{$congress}", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                    'offset' => $offset,
                    'limit' => $limit,
                ]);

            if ($response->failed()) {
                Log::error('Congress API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Congress API exception', [
                'message' => $e->getMessage(),
                'congress' => $congress,
            ]);
            return [];
        }
    }

    /**
     * Get detailed information about a specific bill
     *
     * @param int $congress Congress number
     * @param string $billType Bill type (hr, s, hjres, sjres, hconres, sconres, hres, sres)
     * @param int $billNumber Bill number
     * @return array|null
     */
    public function getBill(int $congress, string $billType, int $billNumber): ?array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill/{$congress}/{$billType}/{$billNumber}", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                Log::error('Congress API bill request failed', [
                    'status' => $response->status(),
                    'congress' => $congress,
                    'billType' => $billType,
                    'billNumber' => $billNumber,
                ]);
                return null;
            }

            return $response->json('bill');
        } catch (\Exception $e) {
            Log::error('Congress API bill exception', [
                'message' => $e->getMessage(),
                'congress' => $congress,
                'billType' => $billType,
                'billNumber' => $billNumber,
            ]);
            return null;
        }
    }

    /**
     * Get actions/events for a specific bill
     *
     * @param int $congress Congress number
     * @param string $billType Bill type
     * @param int $billNumber Bill number
     * @return array
     */
    public function getBillActions(int $congress, string $billType, int $billNumber): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill/{$congress}/{$billType}/{$billNumber}/actions", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                return [];
            }

            return $response->json('actions', []);
        } catch (\Exception $e) {
            Log::error('Congress API actions exception', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get summaries for a specific bill
     *
     * @param int $congress Congress number
     * @param string $billType Bill type
     * @param int $billNumber Bill number
     * @return array
     */
    public function getBillSummaries(int $congress, string $billType, int $billNumber): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill/{$congress}/{$billType}/{$billNumber}/summaries", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                return [];
            }

            return $response->json('summaries', []);
        } catch (\Exception $e) {
            Log::error('Congress API summaries exception', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get cosponsors for a specific bill
     *
     * @param int $congress Congress number
     * @param string $billType Bill type
     * @param int $billNumber Bill number
     * @return array
     */
    public function getBillCosponsors(int $congress, string $billType, int $billNumber): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill/{$congress}/{$billType}/{$billNumber}/cosponsors", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                return [];
            }

            return $response->json('cosponsors', []);
        } catch (\Exception $e) {
            Log::error('Congress API cosponsors exception', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get text versions of a bill
     *
     * @param int $congress Congress number
     * @param string $billType Bill type
     * @param int $billNumber Bill number
     * @return array
     */
    public function getBillText(int $congress, string $billType, int $billNumber): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill/{$congress}/{$billType}/{$billNumber}/text", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                return [];
            }

            return $response->json('textVersions', []);
        } catch (\Exception $e) {
            Log::error('Congress API text exception', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get members of a specific congress
     *
     * @param int $congress Congress number
     * @param string $chamber 'house' or 'senate'
     * @return array
     */
    public function getMembers(int $congress, string $chamber = 'house'): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/member/congress/{$congress}/{$chamber}", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                return [];
            }

            return $response->json('members', []);
        } catch (\Exception $e) {
            Log::error('Congress API members exception', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Search bills by keyword
     *
     * @param string $query Search query
     * @param int $offset Pagination offset
     * @param int $limit Number of results
     * @return array
     */
    public function searchBills(string $query, int $offset = 0, int $limit = 20): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                    'q' => $query,
                    'offset' => $offset,
                    'limit' => $limit,
                ]);

            if ($response->failed()) {
                return [];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Congress API search exception', [
                'message' => $e->getMessage(),
                'query' => $query,
            ]);
            return [];
        }
    }

    /**
     * Get the current congress number
     *
     * @return int
     */
    public function getCurrentCongress(): int
    {
        // 118th Congress: 2023-2025
        // 119th Congress: 2025-2027
        // Formula: Congress number = ((year - 1789) / 2) + 1
        $year = now()->year;
        return (int) floor(($year - 1789) / 2) + 1;
    }

    /**
     * Parse bill identifier from API data
     *
     * @param array $bill API bill data
     * @return string Bill identifier (e.g., "H.R. 1234")
     */
    public function parseBillIdentifier(array $bill): string
    {
        $type = strtoupper($bill['type'] ?? '');
        $number = $bill['number'] ?? '';

        $typeMap = [
            'HR' => 'H.R.',
            'S' => 'S.',
            'HJRES' => 'H.J.Res.',
            'SJRES' => 'S.J.Res.',
            'HCONRES' => 'H.Con.Res.',
            'SCONRES' => 'S.Con.Res.',
            'HRES' => 'H.Res.',
            'SRES' => 'S.Res.',
        ];

        $displayType = $typeMap[$type] ?? $type;
        return "{$displayType} {$number}";
    }

    /**
     * Parse chamber from bill type
     *
     * @param string $billType Bill type code
     * @return string 'house' or 'senate'
     */
    public function parseChamber(string $billType): string
    {
        return str_starts_with(strtolower($billType), 'h') ? 'house' : 'senate';
    }

    /**
     * Get committee assignments for a bill
     *
     * @param int $congress Congress number
     * @param string $billType Bill type
     * @param int $billNumber Bill number
     * @return array
     */
    public function getBillCommittees(int $congress, string $billType, int $billNumber): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill/{$congress}/{$billType}/{$billNumber}/committees", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                Log::warning('Congress API committees request failed', [
                    'bill' => "{$congress}/{$billType}/{$billNumber}",
                    'status' => $response->status(),
                ]);
                return [];
            }

            return $response->json()['committees'] ?? [];
        } catch (\Exception $e) {
            Log::error('Congress API committees exception', [
                'message' => $e->getMessage(),
                'bill' => "{$congress}/{$billType}/{$billNumber}",
            ]);
            return [];
        }
    }

    /**
     * Get subjects/policy areas for a bill
     *
     * @param int $congress Congress number
     * @param string $billType Bill type
     * @param int $billNumber Bill number
     * @return array
     */
    public function getBillSubjects(int $congress, string $billType, int $billNumber): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill/{$congress}/{$billType}/{$billNumber}/subjects", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                Log::warning('Congress API subjects request failed', [
                    'bill' => "{$congress}/{$billType}/{$billNumber}",
                    'status' => $response->status(),
                ]);
                return [];
            }

            return $response->json()['subjects'] ?? [];
        } catch (\Exception $e) {
            Log::error('Congress API subjects exception', [
                'message' => $e->getMessage(),
                'bill' => "{$congress}/{$billType}/{$billNumber}",
            ]);
            return [];
        }
    }

    /**
     * Get all title variations for a bill
     *
     * @param int $congress Congress number
     * @param string $billType Bill type
     * @param int $billNumber Bill number
     * @return array
     */
    public function getBillTitles(int $congress, string $billType, int $billNumber): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/bill/{$congress}/{$billType}/{$billNumber}/titles", [
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ]);

            if ($response->failed()) {
                Log::warning('Congress API titles request failed', [
                    'bill' => "{$congress}/{$billType}/{$billNumber}",
                    'status' => $response->status(),
                ]);
                return [];
            }

            return $response->json()['titles'] ?? [];
        } catch (\Exception $e) {
            Log::error('Congress API titles exception', [
                'message' => $e->getMessage(),
                'bill' => "{$congress}/{$billType}/{$billNumber}",
            ]);
            return [];
        }
    }
}
