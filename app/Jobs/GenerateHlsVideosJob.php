<?php

namespace App\Jobs;

use App\Services\HlsConverterService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateHlsVideosJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $mediaId)
    {
    }

    /**
     * @throws Exception
     */
    public function handle(HlsConverterService $service): void
    {
        try {
            $service->execute($this->mediaId);
        } catch (Exception $e) {
            Log::error("HLS generation error for Media Id: {$this->mediaId}: {$e->getMessage()}");

            throw $e;
        }
    }
}
