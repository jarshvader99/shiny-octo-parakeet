<?php

namespace App\Console\Commands;

use App\Jobs\SyncBillsFromCongressJob;
use Illuminate\Console\Command;

class SyncBillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:sync
                            {--congress= : Congress number (default: 119)}
                            {--type= : Bill type (hr, s, hjres, etc.)}
                            {--limit=20 : Number of bills to sync}
                            {--offset=0 : Pagination offset}
                            {--queue : Run sync in background queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync bills from Congress.gov API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $congress = $this->option('congress') ? (int) $this->option('congress') : null;
        $billType = $this->option('type');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $useQueue = $this->option('queue');

        $this->info('Starting bill sync...');
        $this->info("Congress: " . ($congress ?? '119 (current)'));
        $this->info("Bill Type: " . ($billType ?? 'all'));
        $this->info("Limit: {$limit}");

        if ($useQueue) {
            // Dispatch to queue
            SyncBillsFromCongressJob::dispatch($congress, $billType, $limit, $offset);
            $this->info('Bill sync job dispatched to queue.');
            $this->comment('Monitor queue worker logs for progress.');
        } else {
            // Run synchronously
            $job = new SyncBillsFromCongressJob($congress, $billType, $limit, $offset);
            $job->handle(app(\App\Services\CongressApiClient::class));
            $this->info('✓ Bill sync completed!');
        }

        return Command::SUCCESS;
    }
}
