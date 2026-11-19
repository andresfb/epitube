<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Feed;
use App\Services\Tube\Feed\FeedService;

final readonly class FeedsAction
{
    public function __construct(private FeedService $service) {}

    public function handle(int $page, bool $fromRequest = true): FeedListItem
    {
        $feed = $this->service->getFeed($page, $fromRequest);

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
