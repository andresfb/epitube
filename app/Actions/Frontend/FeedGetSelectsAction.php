<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Enums\Selects;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Category;
use App\Models\Tube\Feed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final readonly class FeedGetSelectsAction
{
    /**
     * Execute the action.
     */
    public function handle(Selects $select, int $page): FeedListItem
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $perPage = Config::integer('feed.per_page');
        $cacheKey = "FEED:CATE:$cateSlug:SELECTS:$select->value:PAGE:$page:$perPage";

        $feed = Cache::tags('feed')
            ->remember(
                md5($cacheKey),
                now()->addHour(),
                function () use ($perPage, $cateSlug, $select): LengthAwarePaginator {
                    $query = Feed::query()
                        ->where('category_id', Category::getId($cateSlug))
                        ->where('active', true)
                        ->orderByDesc('published')
                        ->orderBy('order')
                        ->limit(Config::integer('feed.max_feed_limit'));

                    match ($select) {
                        Selects::FEATURED => $query->where('featured', true),
                        Selects::LIKED => $query->where('like_status', 1),
                        Selects::DISLIKED => $query->where('like_status', -1),
                        default => $query->where('viewed', true),
                    };

                    return $query->paginate($perPage);
                });

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
