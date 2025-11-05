<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Jobs\Tube\ImportRelatedVideosJob;
use App\Services\Tube\ImportRelatedVideosService;
use Illuminate\Console\Command;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class ImportRelatedVideosCommand extends Command
{
    protected $signature = 'import:related';

    protected $description = 'Import the Related Videos from the JellyFin API';

    public function handle(ImportRelatedVideosService $service): void
    {
        clear();
        intro('Starting Import');

        try {
            if (confirm('Dispatch Job', false)) {
                ImportRelatedVideosJob::dispatch();
                $this->line('Job dispatched');

                return;
            }

            $this->line('Running service...');
            $service->handle();
        } catch (Throwable $e) {
            $this->newLine();
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
