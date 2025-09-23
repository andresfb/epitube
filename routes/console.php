<?php

declare(strict_types=1);

use App\Jobs\CreateFeedJob;
use App\Jobs\ImportRelatedVideosJob;
use App\Jobs\ImportVideosJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ImportVideosJob)->dailyAt('22:15');
Schedule::job(new ImportRelatedVideosJob)->dailyAt('01:45');
Schedule::job(new CreateFeedJob)->dailyAt('03:25');
// TODO: add a job to clear all files from the "download" disk. Scheduled it to run at 21:30
