<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Enums\Durations;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Category;
use App\Models\Tube\Feed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final readonly class FeedGetDurationAction
{
    public function handle(Durations $duration, int $page): FeedListItem
    {
        $durations = Durations::list($duration);
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $perPage = Config::integer('feed.per_page');
        $cacheKey = "FEED:CATE:$cateSlug:DURATION:$duration->value:PAGE:$page:$perPage";

        $feed = Cache::tags('feed')
            ->remember(
                md5($cacheKey),
                now()->addHour(),
                function () use ($perPage, $cateSlug, $durations): LengthAwarePaginator {
                    return Feed::query()
                        ->where('category_id', Category::getId($cateSlug))
                        ->where('active', true)
                        ->where('viewed', false)
                        ->where('like_status', '>=', 0)
                        ->whereBetween('length', $durations)
                        ->orderBy('length')
                        ->orderByDesc('published')
                        ->orderBy('order')
                        ->limit(Config::integer('feed.max_feed_limit'))
                        ->paginate($perPage);
                });

        return new FeedListItem(
            $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            $feed->links(),
        );
    }
}
