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

    // TODO: create a `forDetail()` method
    // TODO: add fromContent{Listing,Detail}() method where the argument is the Content
    public static function forListing(Feed $feed): static
    {
        return Cache::tags('feed')
            ->remember(
                md5("FEED:LISTING:ITEM:$feed->slug"),
                now()->addMinutes(5),
                static function () use ($feed): static {
                    $feedArray = $feed->toArray();
                    $thumb = collect($feed->thumbnails)->random();

                    $feedArray['thumbnail'] = $thumb['srcset'];
                    $feedArray['previews'] = $feed->previews;
                    $feedArray['added_at'] = $feed->added_at->diffForHumans();
                    $feedArray['tags'] = $feed->tag_array;
                    sort($feedArray['tags']);
                    $feedArray['videos'] = [];
                    $feedArray['related'] = [];
                    $feedArray['service_url'] = '';

                    return self::from($feedArray);
                }
            );
    }
}
