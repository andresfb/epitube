<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Contracts\FeedMirror;
use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\WatchLater;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ContentWatchLaterAction
{
    public function __construct(private FeedMirror $feedMirror) {}

    /**
     * @throws Throwable
     */
    public function handle(string $slug): bool
    {
        return DB::transaction(function () use ($slug): bool {
            $content = Content::query()
                ->where('slug', $slug)
                ->firstOrFail();

            $existing = WatchLater::query()
                ->where('content_id', $content->id)
                ->first();

            if ($existing !== null) {
                $existing->delete();
                $watchLater = false;
            } else {
                WatchLater::query()->create([
                    'content_id' => $content->id,
                ]);
                $watchLater = true;
            }

            $this->feedMirror->updateBySlug($content->slug, [
                'watch_later' => $watchLater,
            ]);

            CacheLibrary::clear(['feed']);

            return $watchLater;
        });
    }
}
