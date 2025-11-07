<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ContentChangeStatusAction
{
    /**
     * @throws Throwable
     */
    public function handle(string $slug, int $status): int
    {
        return DB::transaction(static function () use ($slug, $status): int {
            if ($status === 0) {
                return $status;
            }

            $content = Content::where('slug', $slug)
                ->firstOrFail();

            $content->like_status = $content->like_status === $status ? 0 : $status;
            $content->updateQuietly();

            Feed::where('slug', $content->slug)
                ->update(['like_status' => $content->like_status]);

            Cache::tags('feed')->flush();

            return $content->fresh()->like_status;
        });
    }
}
