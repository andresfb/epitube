<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final readonly class CreateFeedService
{
    public function __construct(private SyncFeedRecordsService $feedRecordsService) {}

    public function execute(): void
    {
        $this->feedRecordsService->execute();

        Log::notice('Start to create feed');

        $main = $this->getBaseQuery();
        $contents = $main->inMainCategory()->get();
        if ($contents->isEmpty()) {
            Log::error('No unplayed contents found in Main Category');
        }


        $alt = $this->getBaseQuery();
        $altContents = $alt->inAltCategory()->get();
        if ($altContents->isEmpty()) {
            Log::error('No unplayed contents found in the Alt Category');
        }

        $contents = $contents->merge($altContents);
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

    private function getBaseQuery(): Builder|Content
    {
        return Content::query()
            ->with('related')
            ->hasVideos()
            ->hasThumbnails()
            ->where('active', true)
            ->where('viewed', false)
            // TODO: ->where('like_status', '>=' 0)
            ->where('created_at', '<=', now()->addHours(5))
            ->inRandomOrder()
            ->limit(
                (int) floor(Config::integer('feed.max_feed_limit') * 1.5)
            );
    }
}
