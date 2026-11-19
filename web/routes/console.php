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

Schedule::job(resolve(ImportVideosJob::class))->dailyAt('09:20');
Schedule::job(resolve(ImportRelatedVideosJob::class))->dailyAt('01:45');
Schedule::job(resolve(CreateFeedJob::class))->dailyAt('03:25');
Schedule::job(resolve(DeleteDisabledJob::class))->dailyAt('05:35');
Schedule::job(resolve(ImportVideosPathsJob::class))->dailyAt('13:20');
Schedule::job(resolve(UpdateFeedJob::class))->dailyAt('23:05');
Schedule::job(resolve(CheckEncodingErrorsJob::class))->dailyAt('23:45');
Schedule::job(resolve(ClearTemporaryDisksJob::class))->dailyAt('23:55');
