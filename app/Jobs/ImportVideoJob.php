<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Dtos\ImportVideoItem;
use App\Services\ImportVideoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ImportVideoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly ImportVideoItem $videoItem)
    {
        $this->queue = 'ingestor';
        $this->delay = now()->addSeconds(30);
    }

    /**
     * @throws Throwable
     */
    public function handle(ImportVideoService $service): void
    {
        try {
            $service->execute($this->videoItem);
        } catch (Throwable $e) {
            Log::error("Error importing file: {$this->videoItem->Path}: {$e->getMessage()}");

            throw $e;
        }
    }
}
