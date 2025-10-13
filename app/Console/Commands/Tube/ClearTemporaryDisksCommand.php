<?php

namespace App\Console\Commands\Tube;

use App\Jobs\Tube\ClearTemporaryDisksJob;
use App\Libraries\Tube\DiskNamesLibrary;
use App\Services\Tube\ClearDirectoryDiskService;
use App\Services\Tube\ClearDownloadDiskService;
use Exception;
use Illuminate\Console\Command;
use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class ClearTemporaryDisksCommand extends Command
{
    protected $signature = 'clear:temps';

    protected $description = 'Delete pending temp files or directories in the temp paths';

    public function __construct(
        private readonly ClearDownloadDiskService  $downloadDiskService,
        private readonly ClearDirectoryDiskService $directoryDiskService,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Starting Delete...');

            if (confirm('Dispatch Job?', app()->isProduction())) {
                ClearTemporaryDisksJob::dispatch();
                info('Job Dispatched');

                return;
            }

            info('Executing clearing Download Disk service...');
            $this->downloadDiskService->execute();

            info('Executing clearing Processing Disk service...');
            $this->directoryDiskService->execute(DiskNamesLibrary::processing());

            info('Executing clearing Transcode Disk service...');
            $this->directoryDiskService->execute(DiskNamesLibrary::transcode());
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
