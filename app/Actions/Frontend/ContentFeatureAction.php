<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Contracts\FeedMirror;
use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\WatchLater;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ContentFeatureAction
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

            $content->like_status = 1;
            $content->viewed = true;
            $content->view_count++;
            $content->featured = true;
            $content->updateQuietly();
            $content = $content->fresh();

            WatchLater::query()
                ->where('content_id', $content->id)
                ->delete();

            $this->feedMirror->updateBySlug($content->slug, [
                'like_status' => 1,
                'viewed' => true,
                'view_count' => $content->view_count,
                'featured' => true,
                'watch_later' => false,
            ]);

            CacheLibrary::clear(['feed']);
        });
    }
}
