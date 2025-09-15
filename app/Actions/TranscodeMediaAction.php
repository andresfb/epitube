<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\CreatePreviewsJob;
use App\Jobs\GenerateHlsVideosJob;
use App\Jobs\TranscodeVideoJob;
use App\Models\Media;
use App\Models\MimeType;
use Exception;
use Illuminate\Support\Facades\Log;

final readonly class TranscodeMediaAction
{
    /**
     * @throws Exception
     */
    public function handle(Media $media): void
    {
        try {
            $transcodeMineTypes = MimeType::transcode();

            if (! in_array($media->mime_type, $transcodeMineTypes, true)) {
                GenerateHlsVideosJob::dispatch($media->id)
                    ->onQueue('hls')
                    ->delay(now()->addSeconds(15));

                CreatePreviewsJob::dispatch($media->id)
                    ->onQueue('encode')
                    ->delay(now()->addSeconds(15));

                return;
            }

            Log::info("Transcoding $media->model_id | $media->name");

            $media->setCustomProperty('transcode', true);

            TranscodeVideoJob::dispatch($media->id)
                ->onQueue('encode')
                ->delay(now()->addSeconds(15));
        } catch (Exception $e) {
            Log::error("Error transcoding Media Id: {$media->id}: {$e->getMessage()}");

            throw $e;
        }
    }
}
