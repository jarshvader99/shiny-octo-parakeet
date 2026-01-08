<?php

namespace App\Console\Commands;

use App\Jobs\ResyncStaleBillsJob;
use App\Services\CongressApiClient;
use Illuminate\Console\Command;

class ResyncStaleBillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:resync-stale
                            {--limit=50 : Maximum number of bills to re-sync}
                            {--hours=72 : Consider bills stale after this many hours}
                            {--queue : Run the job in the background queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-sync stale bills to pick up new summaries and updates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $hours = (int) $this->option('hours');
        $useQueue = $this->option('queue');

        $this->info('Re-syncing stale bills...');
        $this->info("Limit: {$limit} bills");
        $this->info("Stale threshold: {$hours} hours");

        $job = new ResyncStaleBillsJob($limit, $hours);

        if ($useQueue) {
            dispatch($job);
            $this->info('✓ Job dispatched to queue!');
        } else {
            // Run synchronously
            $job->handle(app(CongressApiClient::class));
            $this->info('✓ Re-sync completed!');
        }

        return Command::SUCCESS;
    }
}
