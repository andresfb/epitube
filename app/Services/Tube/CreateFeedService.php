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
    public function execute(): void
    {
        Log::notice('Start to create feed');

        Log::notice('Unpublishing current Feed');
        Feed::query()
            ->where('published', true)
            ->update([
                'order' => 0,
                'published' => false,
            ]);

        Log::notice('Loading Main Category Contents');
        $main = $this->getBaseQuery();
        $contents = $main->inMainCategory()->get();
        if ($contents->isEmpty()) {
            Log::error('No unplayed contents found in Main Category');
        }

        Log::notice('Loading Alt Category Contents');
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

        $index = 1;
        Log::notice('Activating Feed records');
        foreach ($contents as $content) {
            Feed::activateFeed($content, $index);
            $index++;
        }

        Cache::tags('feed')->flush();
        Log::notice('Finished creating feed');
    }

    private function getBaseQuery(): Builder
    {
        return Content::query()
            ->with('related')
            ->usable()
            ->where('viewed', false)
            ->where('created_at', '<=', now()->addHours(5))
            ->inRandomOrder()
            ->limit(
                (int) ceil(Config::integer('feed.max_feed_limit') * 1.5)
            );
    }
}
