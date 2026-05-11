<?php

use App\Jobs\PublishScheduledArticlesJob;
use App\Jobs\UnpinExpiredArticlesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new UnpinExpiredArticlesJob)->hourly();
Schedule::job(new PublishScheduledArticlesJob)->everyFiveMinutes();
Schedule::command('snapshots:purge')->dailyAt('03:00');
