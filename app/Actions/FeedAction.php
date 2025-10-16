<?php

declare(strict_types=1);

namespace App\Actions;

use App\Dtos\Tube\ContentItem;
use App\Models\Tube\Category;
use App\Models\Tube\Feed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final readonly class FeedAction
{
    /**
     * @return Collection<ContentItem>
     */
    public function handle(int $page): Collection
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $perPage = Config::integer('feed.per_page');
        $cacheKey = "FEED:PAGE:$page:$perPage";

        return Cache::tags('feed')
            ->remember(
                md5($cacheKey),
                now()->addHour(),
                function () use ($perPage, $cateSlug): Collection {
                    return Feed::query()
                        ->where('category_id', Category::getId($cateSlug))
                        ->where('active', true)
                        ->where('viewed', false)
                        ->where('published', true)
                        ->orderBy('order')
                        ->limit(Config::integer('feed.max_feed_limit'))
                        ->paginate($perPage)
                        ->map(function (Feed $item) {
                            return ContentItem::from($item->content);
                        });
                });
    }
}
