<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Media;
use Illuminate\Support\Facades\File;
use RuntimeException;

final readonly class CreateSymLinksAction
{
    public function handle(Media $media): void
    {
        $ogFile = $media->getCustomProperty('og_file', '');
        if (blank($ogFile)) {
            return;
        }

        $mediaPath = $media->getPath();
        if (is_link($mediaPath)) {
            return;
        }

        if (! File::delete($mediaPath)) {
            throw new RuntimeException("Unable to delete media file: $mediaPath");
        }

        if (! symlink($ogFile, $mediaPath)) {
            throw new RuntimeException("Unable to link symlink: $ogFile -> $mediaPath");
        }
    }
}
