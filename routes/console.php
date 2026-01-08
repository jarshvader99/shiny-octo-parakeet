<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule periodic bill re-syncing to pick up new summaries and updates
Schedule::command('bills:resync-stale --limit=100 --hours=72')
    ->daily()
    ->at('02:00')
    ->timezone('America/New_York')
    ->name('resync-stale-bills')
    ->description('Re-sync stale bills to pick up new summaries');

// Schedule new bill syncing from current Congress (119th)
Schedule::command('bills:sync --congress=119 --limit=100 --queue')
    ->everyThreeHours()
    ->timezone('America/New_York')
    ->name('sync-new-119th-bills')
    ->description('Sync latest bills from 119th Congress');

// Weekly full sync to catch any missed bills from current Congress
Schedule::command('bills:sync-all --congress=119 --batch-size=250 --queue --delay=5')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->timezone('America/New_York')
    ->name('weekly-full-sync-119')
    ->description('Weekly full sync of 119th Congress bills');
