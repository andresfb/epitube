<?php

namespace App\Dtos\Tube;

use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelData\Data;

class FeedItem extends Data
{
    public function __construct(
        public int $id,
        public string $slug,
        public int $category_id,
        public string $category,
        public string $title,
        public bool $active,
        public bool $viewed,
        public bool $liked,
        public int $view_count,
        public string $thumbnail,
        public string $duration,
        public string $resolution,
        public bool $is_hd,
        public string $added_at,
        public array $tags = [],
        public array $videos = [],
        public array $previews = [],
        public array $related = [],
    ) {}

    public static function forListing(Feed $feed): static
    {
        return Cache::tags('feed')
            ->remember(
                md5("FEED:LISTING:ITEM:$feed->slug"),
                now()->addMinutes(5),
                static function () use ($feed): static {
                    $feedArray = $feed->toArray();
                    $thumb = collect($feed->thumbnails)->random();

                    $video = [];
                    $height = 0;

                    foreach ($feed->videos as $item) {
                        if ($item['height'] <= $height) {
                            continue;
                        }

                        $video = $item;
                    }

                    $feedArray['thumbnail'] = $thumb['srcset'];
                    $feedArray['duration'] = self::readableDuration($video['duration']);
                    $feedArray['resolution'] = "{$video['height']}p";
                    $feedArray['is_hd'] = $video['height'] >= 720;
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
}
