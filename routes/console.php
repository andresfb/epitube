<?php

declare(strict_types=1);

use App\Jobs\Tube\CheckEncodingErrorsJob;
use App\Jobs\Tube\ClearTemporaryDisksJob;
use App\Jobs\Tube\ImportVideosJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(app(ImportVideosJob::class))->twiceDailyAt(9, 17, 15); // at 9:15 AM and 5:15 PM
//Schedule::job(new ImportRelatedVideosJob)->dailyAt('01:45');
Schedule::job(app(CheckEncodingErrorsJob::class))->dailyAt('23:45');
Schedule::job(app(ClearTemporaryDisksJob::class))->dailyAt('23:55');
//Schedule::job(app(CheckSelectedVideosJob::class))->dailyAt('04:05');
