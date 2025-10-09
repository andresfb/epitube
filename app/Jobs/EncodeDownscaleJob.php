<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\EncodeDownscaleService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class EncodeDownscaleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $resolution,
        private readonly int $mediaId,
    ) {
        $this->queue = 'encode';
        $this->delay = now()->addSeconds(10);
    }

    /**
     * @throws Exception
     */
    public function handle(EncodeDownscaleService $service): void
    {
        try {
            $service->execute(
                resolution: $this->resolution,
                mediaId: $this->mediaId,
            );
        } catch (Exception $e) {
            Log::error(
                "Downscale error for Media Id: $this->mediaId, Resolution: $this->resolution: {$e->getMessage()}"
            );

            throw $e;
        }
    }
}
