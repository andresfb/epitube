<?php

namespace App\Listeners;

use App\Jobs\TranscodeVideoJob;
use App\Models\MimeType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class MediaAddedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(MediaHasBeenAddedEvent $event): void
    {
        if ($event->media === null) {
            return;
        }

        $media = $event->media;
        $transcodeMineTypes = MimeType::transcode();

        if (!in_array($media->mime_type, $transcodeMineTypes, true)) {
            return;
        }

        // TODO: enable transcode job

        Log::info("Transcoding $media->model_id | $media->name");

//        TranscodeVideoJob::dispatch($media->id)
//            ->onConnection('encode')
//            ->onQueue('una')
//            ->delay(now()->addSeconds(15));
    }
}
