<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Dtos\Tube\RandomVideoItem;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Config;

final readonly class RandomVideosAction
{
    public function handle(RandomVideoItem $item): FeedListItem
    {
        $perPage = Config::integer('feed.per_page');

        $query = Content::query()
            ->where('active', true)
            ->where('viewed', false)
            ->where('like_status', '>=', 0)
            ->inRandomOrder()
            ->limit($item->count);

        if ($item->category_id > 0) {
            $query->where('category_id', $item->category_id);
        }

        if (! blank($item->tag)) {
            $query->withAnyTags($item->tag);
        }

        $contentIds = $query->get()
            ->pluck('id')
            ->toArray();

        $feed = Feed::query()
            ->whereIn('id', $contentIds)
            ->paginate($perPage);

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
