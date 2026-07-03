<?php

declare(strict_types=1);

namespace App\Services\Tube\Feed;

use App\Enums\Durations;
use App\Models\Tube\Category;
use App\Models\Tube\Feed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final class FeedDurationService
{
    public function execute(Durations $duration, int $page): LengthAwarePaginator
    {
        $durations = Durations::list($duration);
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $perPage = Config::integer('feed.per_page');
        $cacheKey = "FEED:CATE:{$cateSlug}:DURATION:$duration->value:PAGE:{$page}:{$perPage}";

        return Cache::tags('feed')
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
    }
}
