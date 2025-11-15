<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Category;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

final readonly class VideoSearchAction
{
    public function handle(string $term): FeedListItem
    {
        $cateSlug = Session::get(
            'category',
            Config::string('constants.main_category')
        );

        $feed = Feed::search($term)
            ->where('category_id', Category::getId($cateSlug))
            ->where('active', true)
            ->paginate(
                Config::integer('feed.per_page')
            );

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
