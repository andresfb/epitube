<?php

declare(strict_types=1);

namespace App\Factories;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\VideoItem;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Cache;

final class FeedItemFactory
{
    public static function forListingArray(Feed $feed): array
    {
        return Cache::tags('feed')
            ->remember(
                md5("FEED:LISTING:ITEM:$feed->slug"),
                now()->addMinutes(5),
                static function () use ($feed): array {
                    $feedArray = self::getBaseArray($feed);

                    $feedArray['previews'] = $feed->previews;
                    $feedArray['service_url'] = '';
                    $feedArray['videos'] = [];
                    $feedArray['related'] = [];

                    return $feedArray;
                }
            );
    }

    public static function forDetailArray(Feed $feed): array
    {
        return Cache::tags('feed')
            ->remember(
                md5("FEED:DETAIL:ITEM:$feed->slug"),
                now()->addHour(),
                static function () use ($feed): array {
                    $feedArray = self::getBaseArray($feed);

                    $feedArray['previews'] = [];
                    $feedArray['service_url'] = $feed->service_url;
                    $feedArray['related'] = self::loadRelated($feed);
                    $feedArray['videos'] = collect($feed->videos)
                        ->each(fn (array $video): VideoItem => VideoItem::from($video))
                        ->toArray();

                    return $feedArray;
                }
            );
    }

    public static function forListing(Feed $feed): FeedItem
    {
        return FeedItem::from(
            self::forListingArray($feed)
        );
    }

    public static function forDetail(Feed $feed): FeedItem
    {
        return FeedItem::from(
            self::forDetailArray($feed)
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
        $feedArray['watch_later'] = (bool) ($feedArray['watch_later'] ?? false);

        return $feedArray;
    }

    private static function loadRelated(Feed $feed): array
    {
        if (blank($feed->related)) {
            return [];
        }

        return Feed::query()
            ->whereIn(
                'id',
                collect($feed->related)
                    ->pluck('id')
                    ->toArray()
            )
            ->where('id', '!=', $feed->id)
            ->where('active', true)
            ->where('like_status', '>=', 0)
            ->where('viewed', false)
            ->get()
            ->map(function (Feed $related): FeedItem {
                return self::forListing($related);
            })
            ->toArray();
    }
}
