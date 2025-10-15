<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Jobs\Tube\ImportVideosJob;
use App\Services\Tube\ImportVideosService;
use Exception;
use Illuminate\Console\Command;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class ImportVideosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans the Content path and import the files into the Content Model';

    public function handle(ImportVideosService $service): void
    {
        try {
            clear();
            intro('Starting Import');

            if (confirm('Dispatch Job?', app()->isProduction())) {
                ImportVideosJob::dispatch();
                info('Job Dispatched');

                return;
            }

            info('Executing service...');
            $service->execute();
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
