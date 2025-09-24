<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ImportRelatedVideoService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ImportRelatedVideoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $contentId)
    {
        $this->queue = 'ingestor';
        $this->delay = now()->addSeconds(30);
    }

    /**
     * @throws Exception
     */
    public function handle(ImportRelatedVideoService $service): void
    {
        try {
            $service->execute($this->contentId);
        } catch (Exception $e) {
            Log::error("Import Related Video for Content: $this->contentId got an error: {$e->getMessage()}");

            throw $e;
        }
    }
}
