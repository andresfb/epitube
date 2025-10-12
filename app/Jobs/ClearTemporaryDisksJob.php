<?php

namespace App\Jobs;

use App\Libraries\DiskNamesLibrary;
use App\Services\ClearDownloadDiskService;
use App\Services\ClearDirectoryDiskService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ClearTemporaryDisksJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly ClearDownloadDiskService  $downloadDiskService,
        private readonly ClearDirectoryDiskService $directoryDiskService,
    ) {
        $this->queue = 'ingestor';
        $this->delay = now()->addSeconds(10);
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            $this->downloadDiskService->execute();

            $this->directoryDiskService->execute(DiskNamesLibrary::processing());

            $this->directoryDiskService->execute(DiskNamesLibrary::transcode());
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            Log::error("Error clearing the Download disk: {$e->getMessage()}");

            throw $e;
        }
    }
}
