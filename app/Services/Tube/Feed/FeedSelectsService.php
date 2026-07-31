<?php

declare(strict_types=1);

namespace App\Services\Tube\Feed;

use App\Enums\Selects;
use App\Models\Tube\Category;
use App\Models\Tube\Feed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final class FeedSelectsService
{
    public function execute(Selects $select, int $page): LengthAwarePaginator
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $perPage = Config::integer('feed.per_page');
        $cacheKey = "FEED:CATE:{$cateSlug}:SELECTS:$select->value:PAGE:{$page}:{$perPage}";

        return Cache::tags('feed')
            ->remember(
                md5($cacheKey),
                now()->addHour(),
                function () use ($perPage, $cateSlug, $select): LengthAwarePaginator {
                    $query = Feed::query()
                        ->where('category_id', Category::getId($cateSlug))
                        ->where('active', true)
                        ->orderByDesc('published')
                        ->latest('updated_at')
                        ->limit(Config::integer('feed.max_feed_limit'));

                    match ($select) {
                        Selects::FEATURED => $query->where('featured', true),
                        Selects::LIKED => $query->where('like_status', 1),
                        Selects::DISLIKED => $query->where('like_status', -1),
                        Selects::WATCH_LATER => $query->where('watch_later', true),
                        default => $query->where('viewed', true),
                    };

                    return $query->paginate($perPage);
                });
    }
}
