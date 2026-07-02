<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Dtos\Tube\FeedListItem;
use App\Dtos\Tube\RandomVideoItem;
use App\Factories\FeedItemFactory;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use App\Services\Tube\Feed\FeedService;
use Illuminate\Support\Facades\Config;

final readonly class RandomVideosAction
{
    public function __construct(private FeedService $service) {}

    public function handle(RandomVideoItem $item): FeedListItem
    {
        $feed = $this->service->randomVideos($item);

        return new FeedListItem(
            feed: $feed->map(fn (Feed $feed): FeedItem => FeedItemFactory::forListing($feed)),
            links: $feed->links(),
            total: $feed->total(),
        );
    }
}
