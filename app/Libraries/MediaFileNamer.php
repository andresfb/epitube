<?php

namespace App\Libraries;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

class MediaFileNamer extends DefaultFileNamer
{
    /**
     * originalFileName Method.
     */
    public function originalFileName(string $fileName): string
    {
        return hash('md5', sprintf("%s-%s", $fileName, time()));
    }

    /**
     * extensionFromBaseImage Method.
     */
    public function extensionFromBaseImage(string $baseImage): string
    {
        return strtolower(pathinfo($baseImage, PATHINFO_EXTENSION));
    }

    /**
     * temporaryFileName Method.
     */
    public function temporaryFileName(Media $media, string $extension): string
    {
        return "{$this->responsiveFileName($media->file_name)}." . strtolower($extension);
    }
}
