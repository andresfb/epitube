<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ContentDisableAction
{
    /**
     * @throws Throwable
     */
    public function handle(string $slug): void
    {
        DB::transaction(static function () use ($slug): void {
            $content = Content::where('slug', $slug)
                ->firstOrFail();

            $content->active = false;
            $content->updateQuietly();

            Feed::where('slug', $content->slug)
                ->update(['active' => false]);

            Cache::tags('feed')->flush();
        });
    }
}
