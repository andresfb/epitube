<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Enums\Durations;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Feed;
use App\Services\Tube\Feed\FeedDurationService;

final readonly class FeedGetDurationAction
{
    public function __construct(private FeedDurationService $service) {}

    public function handle(Durations $duration, int $page): FeedListItem
    {
        $feed = $this->service->execute($duration, $page);

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
