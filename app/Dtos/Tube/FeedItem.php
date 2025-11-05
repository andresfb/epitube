<?php

namespace App\Dtos\Tube;

use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelData\Data;

class FeedItem extends Data
{
    public function __construct(
        public int    $id,
        public string $slug,
        public int    $category_id,
        public string $category,
        public string $title,
        public bool   $active,
        public bool   $viewed,
        public int   $like_status,
        public int    $view_count,
        public string $thumbnail,
        public string $duration,
        public string $resolution,
        public bool   $is_hd,
        public string $added_at,
        public array  $tags = [],
        public array  $videos = [],
        public array  $previews = [],
        public array  $related = [],
    ) {}

    public static function forListing(Feed $feed): static
    {
        return Cache::tags('feed')
            ->remember(
                md5("FEED:LISTING:ITEM:$feed->slug"),
                now()->addMinutes(5),
                static function () use ($feed): static {
                    $feedArray = self::getBaseArray($feed);

                    $feedArray['previews'] = $feed->previews;
                    $feedArray['service_url'] = '';
                    $feedArray['videos'] = [];
                    $feedArray['related'] = [];

                    return self::from($feedArray);
                }
            );
    }

    public static function forDetail(Feed $feed): static
    {
        return Cache::tags('feed')
            ->remember(
                md5("FEED:DETAIL:ITEM:$feed->slug"),
                now()->addHour(),
                static function () use ($feed): static {
                    $feedArray = self::getBaseArray($feed);

                    $feedArray['previews'] = [];
                    $feedArray['service_url'] = $feed->service_url;
                    $feedArray['related'] = self::loadRelated($feed);
                    $feedArray['videos'] = collect($feed->videos)
                        ->each(fn (array $video): VideoItem => VideoItem::from($video))
                        ->toArray();

                    return self::from($feedArray);
                }
            );
    }

    public static function getBaseArray(Feed $feed): array
    {
        $feedArray = $feed->toArray();

        $feedArray['tags'] = $feed->tag_array;
        asort($feedArray['tags']);

        $thumb = collect($feed->thumbnails)->random();
        $feedArray['thumbnail'] = $thumb['srcset'];
        $feedArray['added_at'] = $feed->added_at->diffForHumans();

        return $feedArray;
    }

    private static function loadRelated(Feed $feed): array
    {
        if (blank($feed->related)) {
            return [];
        }

        return Feed::query()
            ->whereIn('id', collect($feed->related)->pluck('id')->toArray())
            ->where('id', '!=', $feed->id)
            ->get()
            ->map(function (Feed $related): FeedItem {
                return FeedItem::forListing($related);
            })
            ->toArray();

    }
}
