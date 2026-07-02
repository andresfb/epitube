<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\FeedItem;
use App\Factories\FeedItemFactory;
use App\Services\Tube\Feed\FeedService;

final readonly class FeedAction
{
    public function __construct(private FeedService $service) {}

    public function handle(string $slug): FeedItem
    {
        $feed = $this->service->getVideo($slug);

        return FeedItemFactory::forDetail($feed);
    }
}
