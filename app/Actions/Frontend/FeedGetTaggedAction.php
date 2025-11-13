<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Category;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final readonly class FeedGetTaggedAction
{
    public function handle(string $tagSlug, int $page): FeedListItem
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $perPage = Config::integer('feed.per_page');
        $cacheKey = "TAGGED:$tagSlug:CATE:$cateSlug:PAGE:$page:$perPage";

        $feed = Cache::tags('feed')
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
                        ->limit(Config::integer('feed.max_feed_limit'))
                        ->paginate($perPage);
                });

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
