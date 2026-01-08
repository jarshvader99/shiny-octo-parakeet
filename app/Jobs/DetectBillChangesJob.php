<?php

namespace App\Jobs;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DetectBillChangesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job - Check for stale bills and re-sync them.
     */
    public function handle(): void
    {
        Log::info('Starting bill change detection');

        // Find active bills that haven't been synced recently
        $staleBills = Bill::query()
            ->where('status', '!=', 'became_law')
            ->where('status', '!=', 'failed')
            ->where(function ($query) {
                $query->whereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<', now()->subHours(24));
            })
            ->orderBy('last_synced_at', 'asc')
            ->limit(50)
            ->get();

        Log::info('Found stale bills to re-sync', ['count' => $staleBills->count()]);

        foreach ($staleBills as $bill) {
            // Queue a re-sync for this specific bill
            // In production, this would fetch the latest data from Congress.gov
            SyncBillsFromCongressJob::dispatch(
                congressNumber: $bill->congress_number,
                billType: $bill->bill_type,
                limit: 1,
                offset: 0
            );
        }

        Log::info('Bill change detection completed');
    }
}
