<?php

namespace App\Jobs;

use App\Services\TranscodeVideoService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranscodeVideoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $mediaId) {}

    /**
     * @throws Exception
     */
    public function handle(TranscodeVideoService $service): void
    {
        try {
            $newMediaId = $service->execute($this->mediaId);

            GenerateHlsVideosJob::dispatch($newMediaId)
                ->onQueue('hls')
                ->delay(now()->addSeconds(15));

            CreatePreviewsJob::dispatch($newMediaId)
                ->onQueue('encode')
                ->delay(now()->addSeconds(15));
        } catch (Exception $e) {
            Log::error("Error transcoding file for Media Id: {$this->mediaId}: {$e->getMessage()}");

            throw $e;
        }
    }
}
