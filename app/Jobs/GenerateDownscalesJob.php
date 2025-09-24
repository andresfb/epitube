<?php

namespace App\Jobs;

use App\Services\GenerateDownscalesService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateDownscalesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $mediaId)
    {
        $this->queue = 'encode';
        $this->delay = now()->addSeconds(30);
    }

    /**
     * @throws Exception
     */
    public function handle(GenerateDownscalesService $service): void
    {
        try {
            $service->execute($this->mediaId);
        } catch (Exception $e) {
            Log::error("Downscales error for Media Id: {$this->mediaId}: {$e->getMessage()}");

            throw $e;
        }
    }
}
