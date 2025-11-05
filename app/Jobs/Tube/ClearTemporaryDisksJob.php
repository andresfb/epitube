<?php

declare(strict_types=1);

namespace App\Jobs\Tube;

use App\Libraries\Tube\DiskNamesLibrary;
use App\Services\Tube\ClearDirectoryDiskService;
use App\Services\Tube\ClearDownloadDiskService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ClearTemporaryDisksJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->queue = 'ingestor';
        $this->delay = now()->addSeconds(10);
    }

    /**
     * @throws Exception
     */
    public function handle(
        ClearDownloadDiskService $downloadDiskService,
        ClearDirectoryDiskService $directoryDiskService
    ): void {
        try {
            $downloadDiskService->execute();

            $directoryDiskService->execute(DiskNamesLibrary::processing());

            $directoryDiskService->execute(DiskNamesLibrary::transcode());
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            Log::error("Error clearing the Download disk: {$e->getMessage()}");

            throw $e;
        }
    }
}
