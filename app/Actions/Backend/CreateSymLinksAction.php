<?php

declare(strict_types=1);

namespace App\Actions\Backend;

use App\Traits\Screenable;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class CreateSymLinksAction
{
    use Screenable;

    public function handle(Media $media, bool $skipDelete = false): void
    {
        $this->notice("Replacing video file with symlink for Media Id: $media->id");

        $ogFile = $media->getCustomProperty('og_path', '');
        if (blank($ogFile)) {
            $this->warning("Media $media->id does not have a original path");

            return;
        }

        $mediaPath = $media->getPath();
        if (is_link($mediaPath)) {
            $this->warning("Media $media->id already has a symlink");

            return;
        }

        if ( ! $skipDelete && ! File::delete($mediaPath)) {
            throw new RuntimeException("Unable to delete media file: $mediaPath");
        }

        $mediaDir = dirname($mediaPath);
        if (! is_dir($mediaDir) && ! mkdir($mediaDir, 0777, true) && ! is_dir($mediaDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $mediaDir));
        }

        if (! symlink($ogFile, $mediaPath)) {
            throw new RuntimeException("Unable to link symlink: $ogFile -> $mediaPath");
        }

        $this->notice('Done replacing video file with symlink');
    }
}
