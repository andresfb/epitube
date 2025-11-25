<?php

namespace App\Console\Commands\Tube;

use App\Jobs\Tube\ImportVideosPathsJob;
use App\Services\Tube\ImportVideosPathsService;
use Illuminate\Console\Command;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class ImportVideosPathsCommand extends Command
{
    protected $signature = 'import:video-paths';

    protected $description = 'Import videos from the Extra Video Paths records';

    public function handle(ImportVideosPathsService $service): void
    {
        try {
            clear();
            intro('Starting Import of Extra Video Paths');

            if (confirm('Dispatch Job?', false)) {
                ImportVideosPathsJob::dispatch();
                info('Job Dispatched');

                return;
            }

            $service->setToScreen(true)
                ->execute();
        }  catch (Throwable $e) {
            $this->newLine();
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
