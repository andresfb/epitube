<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
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
            $content = Content::query()
                ->where('slug', $slug)
                ->firstOrFail();

            $content->active = false;
            $content->saveQuietly();

            $feed = Feed::query()
                ->where('slug', $content->slug)
                ->firstOrFail();

            $feed->active = false;
            $feed->save();

            CacheLibrary::clear();
        });
    }
}
