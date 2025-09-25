<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Libraries\MediaNamesLibrary;
use App\Models\Content;
use App\Models\Media;
use App\Models\RelatedContent;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

final class ContentItem extends Data
{
    /**
     * @param  array<string>  $tags
     * @param  array<VideoItem>  $videos
     * @param  array<PreviewItem>  $previews
     * @param  array<ThumbnailItem>  $thumbnails
     * @param  array<ContentItem>  $related
     */
    public function __construct(
        public string $id,
        public int $category_id,
        public string $category,
        public string $title,
        public bool $active,
        public bool $viewed,
        public bool $liked,
        public int $viewCount,
        public string $service_url,
        public Carbon $addedAt,
        public array $tags = [],
        public array $videos = [],
        public array $previews = [],
        public array $thumbnails = [],
        public array $related = [],
    ) {}

    public static function withRelated(Content $content): self
    {
        $contentArray = self::withContent($content)->toArray();

        $contentArray['related'] = $content->related->map(
            fn (RelatedContent $relatedContent): array => self::withContent($relatedContent->content)->toArray()
        );

        return self::from($contentArray);
    }

    public static function withContent(Content $content): self
    {
        $contentArray = $content->toSearchableArray();

        $contentArray[MediaNamesLibrary::thumbnails()] = $content->getMedia(MediaNamesLibrary::thumbnails())
            ->map(fn (Media $media): ThumbnailItem => new ThumbnailItem(
                urls: $media->getResponsiveImageUrls(),
                srcset: $media->getSrcset(),
            ))->toArray();

        $contentArray[MediaNamesLibrary::previews()] = $content->getMedia(MediaNamesLibrary::previews())
            ->map(fn (Media $media): PreviewItem => new PreviewItem(
                fulUrl: $media->getFullUrl(),
                size: (int) $media->getCustomProperty('size'),
                extension: $media->getCustomProperty('extension'),
            ))->toArray();

        $collection = MediaNamesLibrary::videos();
        if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
            $collection = MediaNamesLibrary::transcoded();
        }

        $contentArray[$collection] = $content->getMedia($collection)
            ->map(fn (Media $media): VideoItem => new VideoItem(
                fulUrl: $media->getFullUrl(),
                duration: (int) $media->getCustomProperty('duration'),
                width: (int) $media->getCustomProperty('width'),
                height: (int) $media->getCustomProperty('height'),
            ))->toArray();

        return self::from($contentArray);
    }
}
