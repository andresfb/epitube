<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\ContentItem;
use App\Models\Content;
use App\Models\Feed;
use Illuminate\Support\Facades\Log;

final class CreateFeedService
{
    public function execute(): void
    {
        $contents = Content::query()
            ->inMainCategory()
            ->hasThumbnails()
            ->where('active', true)
            ->where('viewed', false)
            ->inRandomOrder()
            ->limit(500)
            ->get();

        if ($contents->isEmpty()) {
            Log::error('No unplayed contents found');

            return;
        }

        $expires = now()->addDay()->subSecond();
        foreach ($contents as $content) {
            Feed::updateOrCreate([
                'content_id' => $content->id,
            ], [
                'content' => ContentItem::withContent($content)->toArray(),
                'expires_at' => $expires,
            ]);
        }
    }
}
