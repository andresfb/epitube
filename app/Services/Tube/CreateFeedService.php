<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class CreateFeedService
{
    public function execute(): void
    {
        Log::notice('Start to create feed');

        $query = Content::query()
            ->hasVideos()
            ->hasThumbnails()
            ->where('active', true)
            ->where('viewed', false)
            ->inRandomOrder()
            ->limit(
                (int) floor(Config::integer('feed.max_feed_limit') * 1.5)
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
            Log::error('No unplayed contents found');

            return;
        }

        Feed::query()
            ->where('published', true)
            ->update([
                'order' => 0,
                'published' => false,
            ]);

        $index = 1;
        foreach ($contents as $content) {
            Feed::activateFeed($content, $index);
            $index++;
        }

        Cache::tags('feed')->flush();
        Log::notice('Finished creating feed');
    }
}
