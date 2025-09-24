<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\ContentItem;
use App\Models\Content;
use App\Models\Feed;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class CreateFeedService
{
    public function execute(): void
    {
        $query = Content::query()
            ->hasVideos()
            ->hasThumbnails()
            ->where('active', true)
            ->where('viewed', false)
            ->inRandomOrder()
            ->limit(
                Config::integer('content.max_feed_limit')
            );

        $contents = $query->inMainCategory()->get();
        if ($contents->isEmpty()) {
            Log::error('No unplayed contents found in Main Category');
        }

        $altContents = $query->inAltCategory()->get();
        if ($altContents->isEmpty()) {
            Log::error('No unplayed contents found in the Alt Category');
        }

        $contents->append($altContents);
        if ($contents->isEmpty()) {
            return;
        }

        foreach ($contents as $content) {
            Feed::generate($content);
        }
    }
}
