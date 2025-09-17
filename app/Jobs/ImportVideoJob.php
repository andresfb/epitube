<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Dtos\VideoItem;
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

    public function __construct(private readonly VideoItem $videoItem)
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
            $service->execute($this->videoItem);
        } catch (Exception $e) {
            Log::error("Error importing file: {$this->videoItem->Path}: {$e->getMessage()}");

            throw $e;
        }
    }
}
