<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Jobs\Tube\ExtractThumbnailsJob;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Traits\MediaGetter;
use Illuminate\Support\Facades\Log;

final class RegenerateThumbsService
{
    use MediaGetter;

    public function execute(int $contentId): void
    {
        try {
            Log::info("Regenerate Thumbnails started for Content Id: {$contentId}");

            $content = Content::query()
                ->where('id', $contentId)
                ->firstOrFail();

            $medias = $content->getMedia(MediaNamesLibrary::thumbnails());

            if ($medias->isEmpty()) {
                Log::error("Content Id: {$contentId} has no thumbnails");
                $media = $this->getMedia($content);

                Log::notice('Dispatching Thumbnails Job');
                ExtractThumbnailsJob::dispatch($media->id);

                return;
            }

            $isJpeg = false;
            $jpgMimes = [
                'image/jpeg',
                'image/jpg',
            ];

            Log::notice('Deleting old thumbnails...');
            foreach ($medias as $media) {
                if (in_array($media->mime_type, $jpgMimes, true)) {
                    $isJpeg = true;

                    break;
                }

                $media->forceDelete();
            }

            if ($isJpeg) {
                Log::notice("Thumbnails for Content Id: $contentId are JPGs already");

                return;
            }

            $media = $this->getMedia($content);

            Log::notice('Dispatching Thumbnails Job');
            ExtractThumbnailsJob::dispatch($media->id);
        } finally {
            Log::notice("Regenerate Thumbnails done for Content Id: $contentId");
        }
    }
}
