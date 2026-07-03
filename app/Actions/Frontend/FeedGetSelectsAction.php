<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Enums\Selects;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Feed;
use App\Services\Tube\Feed\FeedSelectsService;

final readonly class FeedGetSelectsAction
{
    public function __construct(private FeedSelectsService $service) {}

    /**
     * Execute the action.
     */
    public function handle(Selects $select, int $page): FeedListItem
    {
        $feed = $this->service->execute($select, $page);

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
