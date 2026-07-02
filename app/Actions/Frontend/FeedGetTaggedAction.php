<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Feed;
use App\Services\Tube\Feed\FeedService;

final readonly class FeedGetTaggedAction
{
    public function __construct(private FeedService $service) {}

    public function handle(string $tagSlug, int $page): FeedListItem
    {
        $feed = $this->service->videosByTag($tagSlug, $page);

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
