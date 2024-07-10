<?php

namespace App\Listeners;

use App\Jobs\TranscodeVideoJob;
use App\Models\MimeType;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class TranscodeMediaListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @throws Exception
     */
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        if ($event->media === null) {
            return;
        }

        try {
            $media = $event->media;
            $transcodeMineTypes = MimeType::transcode();

            if (!in_array($media->mime_type, $transcodeMineTypes, true)) {
                return;
            }

            Log::info("Transcoding $media->model_id | $media->name");

            $media->setCustomProperty('transcode', true);

            TranscodeVideoJob::dispatch($media->id)
                ->onConnection('encode')
                ->onQueue('una')
                ->delay(now()->addSeconds(15));
        } catch (Exception $e) {
            Log::error("Error transcoding Media Id: {$event->media->id}: {$e->getMessage()}");

            throw $e;
        }
    }
}
