<?php

namespace App\Factories;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\VideoItem;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Cache;

class FeedItemFactory
{
    public static function forListing(Feed $feed): FeedItem
    {
        return Cache::tags('feed')
            ->remember(
                md5("FEED:LISTING:ITEM:$feed->slug"),
                now()->addMinutes(5),
                static function () use ($feed): FeedItem {
                    $feedArray = self::getBaseArray($feed);

                    $feedArray['previews'] = $feed->previews;
                    $feedArray['service_url'] = '';
                    $feedArray['videos'] = [];
                    $feedArray['related'] = [];

                    return FeedItem::from($feedArray);
                }
            );
    }

    public static function forDetail(Feed $feed): FeedItem
    {
        return Cache::tags('feed')
            ->remember(
                md5("FEED:DETAIL:ITEM:$feed->slug"),
                now()->addHour(),
                static function () use ($feed): FeedItem {
                    $feedArray = self::getBaseArray($feed);

                    $feedArray['previews'] = [];
                    $feedArray['service_url'] = $feed->service_url;
                    $feedArray['related'] = self::loadRelated($feed);
                    $feedArray['videos'] = collect($feed->videos)
                        ->each(fn (array $video): VideoItem => VideoItem::from($video))
                        ->toArray();

                    return FeedItem::from($feedArray);
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
                return self::forListing($related);
            })
            ->toArray();
    }
}
