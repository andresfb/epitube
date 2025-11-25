<?php

declare(strict_types=1);

use App\Jobs\Tube\CheckEncodingErrorsJob;
use App\Jobs\Tube\ClearTemporaryDisksJob;
use App\Jobs\Tube\CreateFeedJob;
use App\Jobs\Tube\DeleteDisabledJob;
use App\Jobs\Tube\ImportRelatedVideosJob;
use App\Jobs\Tube\ImportVideosJob;
use App\Jobs\Tube\ImportVideosPathsJob;
use App\Jobs\Tube\UpdateFeedJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 3 times a day (every 8 hours) at 1:20 AM, 9:20 AM, and 5:20 PM
Schedule::job(app(ImportVideosJob::class))->cron('20 1,9,17 * * *');
// 2 times a day at 5:20 AM and 1:20 PM
Schedule::job(app(ImportVideosPathsJob::class))->cron('20 5,13 * * *');
Schedule::job(app(ImportRelatedVideosJob::class))->dailyAt('01:45');
Schedule::job(app(CreateFeedJob::class))->dailyAt('03:25');
Schedule::job(app(DeleteDisabledJob::class))->dailyAt('05:35');
Schedule::job(app(UpdateFeedJob::class))->dailyAt('23:05');
Schedule::job(app(CheckEncodingErrorsJob::class))->dailyAt('23:45');
Schedule::job(app(ClearTemporaryDisksJob::class))->dailyAt('23:55');
