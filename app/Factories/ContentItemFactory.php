<?php

namespace App\Factories;

use App\Dtos\Tube\ContentItem;
use App\Dtos\Tube\ThumbnailItem;
use App\Dtos\Tube\VideoItem;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JsonException;

class ContentItemFactory
{
    public static function forListing(Content $content): ContentItem
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

                    return ContentItem::from($contentArray);
                });
    }

    public static function withRelated(Content $content): ContentItem
    {
        $contentArray = self::withContent($content)->toArray();
        $contentArray['related'] = $content->getRelatedIds();

        return ContentItem::from($contentArray);
    }

    public static function withFormatedTags(Content $content): ContentItem
    {
        $contentArray = self::withContent($content)->toArray();

        $contentArray['tag_list'] = self::prepareTags($contentArray['tag_array']);

        return ContentItem::from($contentArray);
    }

    public static function withContent(Content $content): ContentItem
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

        return ContentItem::from($contentArray);
    }

    public static function prepareTags(array $tags): string
    {
        $list = [];

        foreach ($tags as $slug => $tag) {
            $list[] = [
                'value' => $tag,
                'code' => $slug,
            ];
        }

        try {
            return json_encode($list, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::error($e->getMessage());

            return '';
        }
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
