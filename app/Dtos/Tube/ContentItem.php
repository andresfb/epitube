<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Media;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelData\Data;

final class ContentItem extends Data
{
    /**
     * @param  array<string>  $tags
     * @param  array<VideoItem>  $videos
     * @param  array<VideoItem>  $previews
     * @param  array<ThumbnailItem>  $thumbnails
     * @param  array<int>  $related
     */
    public function __construct(
        public int $id,
        public string $slug,
        public int    $category_id,
        public string $category,
        public string $title,
        public string $duration,
        public string $resolution,
        public int    $length,
        public bool   $is_hd,
        public bool   $active,
        public bool   $viewed,
        public int    $like_status,
        public int    $view_count,
        public string $service_url,
        public Carbon $added_at,
        public Carbon $created_at,
        public array  $tags = [],
        public array  $tag_slugs = [],
        public array  $tag_array = [],
        public array  $videos = [],
        public array  $previews = [],
        public array  $thumbnails = [],
        public array  $related = [],
    ) {}

    public static  function forListing(Content $content): self
    {
        return Cache::tags('content')
            ->remember(
                md5("CONTENT:LISTING:ITEM:$content->slug"),
                now()->addHours(8),
                static function () use ($content): ContentItem {
                    $contentArray = $content->toFeedArray();

                    $contentArray[MediaNamesLibrary::thumbnails()] = self::loadThumbnails($content);
                    $contentArray[MediaNamesLibrary::videos()] = self::loadVideos($content);
                    $contentArray = self::getVideoInfo($contentArray);

                    $contentArray[MediaNamesLibrary::thumbnails()] = [];
                    $contentArray['tag_slugs'] = [];
                    $contentArray['tags'] = [];

                    return self::from($contentArray);
                });
    }

    public static function withRelated(Content $content): self
    {
        $contentArray = self::withContent($content)->toArray();
        $contentArray['related'] = $content->getRelatedIds();

        return self::from($contentArray);
    }

    public static function withContent(Content $content): self
    {
        $contentArray = $content->toFeedArray();

        $contentArray[MediaNamesLibrary::thumbnails()] = self::loadThumbnails($content);

        $contentArray[MediaNamesLibrary::previews()] = $content->getMedia(MediaNamesLibrary::previews())
            ->map(fn (Media $media): VideoItem => new VideoItem(
                url: $media->getFullUrl(),
                mimeType: $media->mime_type,
            ))->toArray();

        $contentArray[MediaNamesLibrary::videos()] = self::loadVideos($content);
        $contentArray = self::getVideoInfo($contentArray);

        return self::from($contentArray);
    }

    private static function getVideoInfo(array $contentArray): array
    {
        $height = 0;
        $video = [];

        foreach ($contentArray[MediaNamesLibrary::videos()] as $item) {
            if ($height > $item['height']) {
                continue;
            }

            $height = $item['height'];
            $video = $item;
        }

        if (blank($video)) {
            $contentArray['length'] = 0;
            $contentArray['duration'] = '';
            $contentArray['resolution'] = '';
            $contentArray['is_hd'] = false;

            return $contentArray;
        }

        $contentArray['length'] = $video['duration'];
        $contentArray['duration'] = self::readableDuration($video['duration']);
        $contentArray['resolution'] = "{$video['height']}p";
        $contentArray['is_hd'] = $video['height'] >= 720;

        return $contentArray;
    }

    private static function readableDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            // Example: "1 hour 20 minutes"
            return trim(sprintf('%d h %d min',
                $hours,
                $minutes,
            ));
        }

        // Example: "45 minutes"
        return sprintf('%d min', $minutes);
    }

    private static function loadVideos(Content $content): array
    {
        $collection = MediaNamesLibrary::videos();
        if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
            $collection = MediaNamesLibrary::transcoded();
        }

        $videos = $content->getMedia($collection)
            ->map(fn (Media $media): VideoItem => new VideoItem(
                url: $media->getFullUrl(),
                mimeType: $media->mime_type,
                duration: (int) $media->getCustomProperty('duration'),
                height: (int) $media->getCustomProperty('height'),
            ));

        if ($videos->isEmpty()) {
            $videos = collect();
        }

        if (! $content->hasMedia(MediaNamesLibrary::downscaled())) {
            return $videos->toArray();
        }

        $downscales = $content->getMedia(MediaNamesLibrary::downscaled())
            ->map(fn (Media $media): VideoItem => new VideoItem(
                url: $media->getFullUrl(),
                mimeType: $media->mime_type,
                duration: (int) $media->getCustomProperty('duration'),
                height: (int) $media->getCustomProperty('height'),
            ));

        return $videos->merge($downscales)->toArray();
    }

    private static function loadThumbnails(Content $content): array
    {
        return $content->getMedia(MediaNamesLibrary::thumbnails())
            ->map(fn (Media $media): ThumbnailItem => new ThumbnailItem(
                urls: $media->getResponsiveImageUrls(),
                srcset: $media->getSrcset(),
            ))->toArray();
    }
}
