<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Factories\FeedItemFactory;
use App\Jobs\Tube\CreateFeedJob;
use App\Models\Tube\Category;
use App\Models\Tube\Feed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final readonly class FeedsAction
{
    public function handle(int $page, bool $fromRequest = true): FeedListItem
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $perPage = Config::integer('feed.per_page');
        $cacheKey = "FEED:CATE:$cateSlug:PAGE:$page:$perPage";

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

        if ($feed->isEmpty()) {
            CreateFeedJob::dispatch(
                fromRequest: $fromRequest
            );
        }

        return new FeedListItem(
            $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            $feed->links(),
        );
    }
}
