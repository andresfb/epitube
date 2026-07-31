<?php

declare(strict_types=1);

namespace App\Services\Tube\Feed;

use App\Dtos\Tube\VideoSearchItem;
use App\Models\Tube\Category;
use App\Models\Tube\Feed;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Meilisearch\Client;

final class VideoSearchService
{
    public function execute(VideoSearchItem $item): LengthAwarePaginator
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $client = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key')
        );

        $index = $client->index((new Feed)->searchableAs());

        $ids = collect(
            $index->search($item->term, [
                'limit' => Config::integer('feed.per_page') * 50,
            ])->getHits()
        );

        return Feed::query()
            ->whereIn('id', $ids->pluck('id')->all())
            ->where('category_id', Category::getId($cateSlug))
            ->where('active', true)
            ->where('viewed', $item->viewed)
            ->where('like_status', '>=', $item->liked)
            ->orderByDesc('added_at')
            ->paginate(
                Config::integer('feed.per_page')
            );
    }
}
