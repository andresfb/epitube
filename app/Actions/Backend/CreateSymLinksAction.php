<?php

declare(strict_types=1);

namespace App\Actions\Backend;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final readonly class CreateSymLinksAction
{
    public function handle(Media $media): void
    {
        Log::notice('Replacing video file with symlink');

        $ogFile = $media->getCustomProperty('og_path', '');
        if (blank($ogFile)) {
            Log::warning("Media $media->id does not have a original path");

            return;
        }

        $mediaPath = $media->getPath();
        if (is_link($mediaPath)) {
            Log::warning("Media $media->id already has a symlink");

            return;
        }

        if (! File::delete($mediaPath)) {
            throw new RuntimeException("Unable to delete media file: $mediaPath");
        }

        if (! symlink($ogFile, $mediaPath)) {
            throw new RuntimeException("Unable to link symlink: $ogFile -> $mediaPath");
        }

        Log::notice('Done replacing video file with symlink');
    }
}
