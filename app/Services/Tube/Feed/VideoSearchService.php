<?php

declare(strict_types=1);

namespace App\Services\Tube\Feed;

use App\Dtos\Tube\VideoSearchItem;
use App\Models\Tube\Category;
use App\Models\Tube\Feed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final class VideoSearchService
{
    public function execute(VideoSearchItem $item): LengthAwarePaginator
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $ids = Cache::tags('feed')
            ->remember(
                md5("FEED:SEARCH:TERM:{$item->term}:CATE:{$cateSlug}:VIEWED:{$item->viewed}"),
                now()->addHour(),
                static function () use ($item, $cateSlug): array {
                    return Feed::search($item->term)
                        ->where('category_id', Category::getId($cateSlug))
                        ->where('active', true)
                        ->where('viewed', $item->viewed)
                        ->get()
                        ->pluck('id')
                        ->toArray();
                }
            );

        return Feed::query()
            ->whereIn('id', $ids)
            ->where('like_status', '>=', $item->liked)
            ->paginate(
                Config::integer('feed.per_page')
            );
    }
}
