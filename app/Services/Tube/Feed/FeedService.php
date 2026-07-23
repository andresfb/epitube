<?php

declare(strict_types=1);

namespace App\Services\Tube\Feed;

use App\Dtos\Tube\RandomVideoItem;
use App\Jobs\Tube\CreateFeedJob;
use App\Models\Tube\Category;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final class FeedService
{
    public function getFeed(int $page, bool $fromRequest): LengthAwarePaginator
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $perPage = Config::integer('feed.per_page');
        $cacheKey = "FEED:CATE:{$cateSlug}:PAGE:{$page}:{$perPage}";

        $feed = Cache::tags('feed')
            ->remember(
                md5($cacheKey),
                now()->addHour(),
                function () use ($perPage, $cateSlug): LengthAwarePaginator {
                    return Feed::query()
                        ->where('category_id', Category::getId($cateSlug))
                        ->where('active', true)
                        ->where('viewed', false)
                        ->where('published', true)
                        ->where('like_status', '>=', 0)
                        ->orderBy('order')
                        ->limit(Config::integer('feed.max_feed_limit'))
                        ->paginate($perPage);
                });

        if (! $feed->isEmpty()) {
            return $feed;
        }

        CreateFeedJob::dispatch(
            fromRequest: $fromRequest
        );

        return $feed;
    }

    public function randomVideos(RandomVideoItem $filters): LengthAwarePaginator
    {
        $perPage = Config::integer('feed.per_page');

        $query = Content::query()
            ->where('active', true)
            ->where('viewed', false)
            ->where('like_status', '>=', 0)
            ->inRandomOrder()
            ->limit($filters->count);

        if ($filters->category_id > 0) {
            $query->where('category_id', $filters->category_id);
        }

        if (filled($filters->tag)) {
            $query->withAnyTags($filters->tag);
        }

        $contentIds = $query->get()
            ->pluck('id')
            ->toArray();

        return Feed::query()
            ->whereIn('id', $contentIds)
            ->paginate($perPage);
    }

    public function videosByTag(string $tagSlug, int $page): LengthAwarePaginator
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $perPage = Config::integer('feed.per_page');
        $cacheKey = "TAGGED:{$tagSlug}:CATE:{$cateSlug}:PAGE:{$page}:{$perPage}";

        return Cache::tags('feed')
            ->remember(
                md5($cacheKey),
                now()->addHour(),
                function () use ($perPage, $cateSlug, $tagSlug): LengthAwarePaginator {
                    $contents = Content::query()
                        ->select('slug')
                        ->where('category_id', Category::getId($cateSlug))
                        ->where('active', true)
                        ->where('viewed', false)
                        ->where('like_status', '>=', 0)
                        ->withAnyTags([$tagSlug])
                        ->pluck('slug')
                        ->toArray();

                    return Feed::query()
                        ->whereIn('slug', $contents)
                        ->latest('added_at')
                        ->limit(Config::integer('feed.max_feed_limit'))
                        ->paginate($perPage);
                });
    }

    public function getVideo(string $slug): Feed
    {
        $feed = $this->loadVideo($slug);
        if (! $feed instanceof Feed) {
            return $this->generateFeed($slug);
        }

        return $feed;
    }

    private function generateFeed(string $slug): Feed
    {
        $content = Content::query()
            ->usable()
            ->where('slug', $slug)
            ->firstOrFail();

        Feed::activateFeed($content);

        $feed = $this->loadVideo($slug);
        if (! $feed instanceof Feed) {
            throw (new ModelNotFoundException)->setModel(Feed::class);
        }

        return $feed;
    }

    private function loadVideo(string $slug): ?Feed
    {
        return Feed::query()
            ->where('active', true)
            ->where('slug', $slug)
            ->first();
    }
}
