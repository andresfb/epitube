<?php

namespace App\Listeners;

use App\Jobs\GenerateHlsVideosJob;
use App\Models\MimeType;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class ConvertToHlsListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
    }

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
            $transcodeMineTypes = MimeType::canHls();

            if (!in_array($media->mime_type, $transcodeMineTypes, true)) {
                return;
            }

            GenerateHlsVideosJob::dispatch($media->id)
                ->onQueue('media')
                ->delay(now()->addSeconds(15));
        } catch (Exception $e) {
            Log::error("Error transcoding Media Id: {$event->media->id}: {$e->getMessage()}");

            throw $e;
        }
    }
}
