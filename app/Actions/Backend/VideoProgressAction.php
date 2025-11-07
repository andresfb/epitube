<?php

declare(strict_types=1);

namespace App\Actions\Backend;

use App\Dtos\Tube\VideoProgressItem;
use App\Models\Tube\Content;
use App\Models\Tube\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class VideoProgressAction
{
    /**
     * @throws Throwable
     */
    public function handle(string $slug, VideoProgressItem $item): void
    {
        DB::transaction(function () use ($slug, $item): void {
            $content = Content::where('slug', $slug)
                ->firstOrFail();

            View::create([
                'content_id' => $content->id,
                'seconds_played' => $item->current_time,
            ]);

            $this->markViewed($item, $content);
        });
    }

    private function markViewed(VideoProgressItem $item, Content $content): void
    {
        $viewed = floor($item->current_time / $item->duration) * 100;
        $viewedThreshold = Config::float('content.viewed_threshold');

        if (! $item->completed && $viewed < $viewedThreshold) {
            return;
        }

        $content->viewed = true;
        ++$content->view_count;
        $content->update();

        Cache::tags('feed')->flush();
    }
}
