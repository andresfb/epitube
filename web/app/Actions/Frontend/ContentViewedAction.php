<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Contracts\FeedMirror;
use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\WatchLater;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ContentViewedAction
{
    public function __construct(private FeedMirror $feedMirror) {}

    /**
     * @throws Throwable
     */
    public function handle(string $slug): void
    {
        DB::transaction(function () use ($slug): void {
            $content = Content::query()
                ->where('slug', $slug)
                ->firstOrFail();

            $content->viewed = true;
            $content->view_count++;
            $content->updateQuietly();

            WatchLater::query()
                ->where('content_id', $content->id)
                ->delete();

            $this->feedMirror->updateBySlug($content->slug, [
                'viewed' => true,
                'view_count' => $content->view_count,
                'watch_later' => false,
            ]);

            CacheLibrary::clear(['feed']);
        });
    }
}
