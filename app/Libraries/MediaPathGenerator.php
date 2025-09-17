<?php

declare(strict_types=1);

namespace App\Libraries;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

final class MediaPathGenerator implements PathGenerator
{
    /**
     * getPath Method.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    /**
     * getPathForConversions Method.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    /**
     * getPathForResponsiveImages Method.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /**
     * getBasePath Method.
     */
    private function getBasePath(Media $media): string
    {
        $contentId = mb_str_pad((string) $media->model_id, 12, '0', STR_PAD_LEFT);

        return Str::of(
            collect(mb_str_split($contentId, 3))
                ->reverse()
                ->implode('/')
        )
            ->append('/')
            ->append($media->collection_name)
            ->append('/')
            ->append((string) $media->id)
            ->toString();
    }
}
