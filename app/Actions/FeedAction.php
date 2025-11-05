<?php

declare(strict_types=1);

namespace App\Actions;

use App\Dtos\Tube\FeedItem;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class FeedAction
{
    public function handle(string $slug): FeedItem
    {
        $feed = $this->getFeed($slug);
        if (! $feed instanceof Feed) {
            return $this->generateFeed($slug);
        }

        return FeedItem::forDetail($feed);
    }

    private function generateFeed(string $slug): FeedItem
    {
        $content = Content::query()
            ->usable()
            ->where('slug', $slug)
            ->firstOrFail();

        Feed::activateFeed($content);

        $feed = $this->getFeed($slug);
        if (! $feed instanceof Feed) {
            throw (new ModelNotFoundException)->setModel(Feed::class);
        }

        return FeedItem::forDetail($feed);
    }

    private function getFeed(string $slug): ?Feed
    {
        return Feed::query()
            ->where('active', true)
            ->where('slug', $slug)
            ->first();
    }
}
