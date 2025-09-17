<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ImportVideoService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ImportVideoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly array $fileData)
    {
        $this->queue = 'ingestor';
        $this->delay = now()->addSeconds(15);
    }

    /**
     * @throws Exception
     */
    public function handle(ImportVideoService $service): void
    {
        try {
            $service->execute($this->fileData);
        } catch (Exception $e) {
            Log::error("Error importing file: {$this->fileData['file']}: {$e->getMessage()}");

            throw $e;
        }
    }
}
