<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Dtos\Tube\VideoSearchItem;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Feed;
use App\Services\Tube\Feed\VideoSearchService;

final readonly class VideoSearchAction
{
    public function __construct(private VideoSearchService $service) {}

    public function handle(VideoSearchItem $item): FeedListItem
    {
        $feed = $this->service->execute($item);

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
