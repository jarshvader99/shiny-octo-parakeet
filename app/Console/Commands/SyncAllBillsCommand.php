<?php

namespace App\Console\Commands;

use App\Jobs\SyncBillsFromCongressJob;
use App\Services\CongressApiClient;
use Illuminate\Console\Command;

class SyncAllBillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:sync-all
                            {--congress= : Congress number (default: current)}
                            {--batch-size=250 : Number of bills per batch}
                            {--queue : Run batches in background queue}
                            {--delay=0 : Delay between batches in seconds (to avoid rate limits)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all bills from a Congress in batches';

    /**
     * Execute the console command.
     */
    public function handle(CongressApiClient $api)
    {
        $congress = $this->option('congress') ? (int) $this->option('congress') : $api->getCurrentCongress();
        $batchSize = (int) $this->option('batch-size');
        $useQueue = $this->option('queue');
        $delay = (int) $this->option('delay');

        $this->info("Syncing all bills from {$congress}th Congress...");

        // Get total count from first API call
        $response = $api->getBills($congress, 0, 1);
        $totalAvailable = $response['pagination']['count'] ?? 0;

        if ($totalAvailable === 0) {
            $this->error('No bills found for this Congress.');
            return Command::FAILURE;
        }

        $this->info("Total bills available: {$totalAvailable}");

        $batches = ceil($totalAvailable / $batchSize);
        $this->info("Will process {$batches} batches of {$batchSize} bills each");

        if (!$this->confirm('Do you want to continue?', true)) {
            return Command::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($batches);
        $progressBar->start();

        for ($i = 0; $i < $batches; $i++) {
            $offset = $i * $batchSize;

            if ($useQueue) {
                // Dispatch to queue with delay between batches
                SyncBillsFromCongressJob::dispatch($congress, null, $batchSize, $offset)
                    ->delay(now()->addSeconds($i * $delay));
            } else {
                // Run synchronously
                $job = new SyncBillsFromCongressJob($congress, null, $batchSize, $offset);
                $job->handle($api);

                // Add delay to avoid rate limits
                if ($delay > 0 && $i < $batches - 1) {
                    sleep($delay);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($useQueue) {
            $this->info("✓ {$batches} sync jobs queued successfully!");
            $this->comment("Monitor with: php artisan queue:work");
        } else {
            $this->info("✓ All bills synced successfully!");
        }

        // Show final stats
        $this->showStats($congress);

        return Command::SUCCESS;
    }

    /**
     * Display sync statistics
     */
    private function showStats(int $congress): void
    {
        $total = \App\Models\Bill::where('congress_number', $congress)->count();
        $withSummary = \App\Models\Bill::where('congress_number', $congress)->whereNotNull('summary')->count();
        $withCommittees = \App\Models\Bill::where('congress_number', $congress)->whereNotNull('committees')->count();
        $withSubjects = \App\Models\Bill::where('congress_number', $congress)->whereNotNull('subjects')->count();

        $this->newLine();
        $this->info("Final Statistics for {$congress}th Congress:");
        $this->table(
            ['Metric', 'Count', 'Percentage'],
            [
                ['Total Bills', $total, '100%'],
                ['With Summaries', $withSummary, $total > 0 ? round(($withSummary / $total) * 100, 1) . '%' : '0%'],
                ['With Committees', $withCommittees, $total > 0 ? round(($withCommittees / $total) * 100, 1) . '%' : '0%'],
                ['With Subjects', $withSubjects, $total > 0 ? round(($withSubjects / $total) * 100, 1) . '%' : '0%'],
            ]
        );
    }
}
