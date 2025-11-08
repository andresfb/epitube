<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ContentViewedAction
{
    /**
     * @throws Throwable
     */
    public function handle(string $slug): void
    {
        DB::transaction(static function () use ($slug): void {
            $content = Content::where('slug', $slug)
                ->firstOrFail();

            $content->viewed = true;
            $content->view_count++;
            $content->updateQuietly();

            Feed::where('slug', $content->slug)
                ->update([
                    'viewed' => true,
                    'view_count' => $content->view_count,
                ]);

            Cache::tags('feed')->flush();
        });
    }
}
