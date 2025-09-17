<?php

namespace App\Dtos;

use App\Libraries\MediaNamesLibrary;
use App\Models\Content;
use App\Models\Media;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

final class ContentItem extends Data
{
    public function __construct(
        public string $id,
        public int $category_id,
        public string $category,
        public string $title,
        public bool $active,
        public bool $viewed,
        public bool $liked,
        public int $viewCount,
        public Carbon $addedAt,
        public array $tags = [],
        public array $videos = [],
        public array $previews = [],
        public array $thumbnails = [],
    ) {}

    public static function withContent(Content $content): self
    {
        $contentArray = $content->toSearchableArray();

        $content->media()->each(function (Media $media) use (&$contentArray): void {
            if ($media->collection_name === MediaNamesLibrary::thumbnails()) {
                $contentArray[$media->collection_name][] = [
                    'urls' => $media->getResponsiveImageUrls(),
                    'srcset' => $media->getSrcset()
                ];

                return;
            }

            if ($media->collection_name === MediaNamesLibrary::previews()) {
                $contentArray[$media->collection_name][] = $media->getFullUrl();

                return;
            }

            $contentArray['videos'][$media->collection_name][] = [
                'full' => $media->getFullUrl(),
                'hls' => $media->hasGeneratedConversion('hls')
                    ? $media->getGeneratedConversions()->toArray()
                    : [],
            ];
        });

        return self::from($contentArray);
    }
}
