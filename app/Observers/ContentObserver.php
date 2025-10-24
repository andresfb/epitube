<?php

namespace App\Observers;

use App\Jobs\Tube\SyncFeedJob;
use App\Models\Tube\Content;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContentObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Content $content): void
    {
        $cacheKey = md5("CONTENT:SAVED:$content->id");
        if (Cache::has($cacheKey)) {
            Log::notice("Content '$content->id' already queued for syncing");

            return;
        }

        Cache::put($cacheKey, true, now()->addSeconds(6));
        Log::notice("Syncing content$content->id");
        SyncFeedJob::dispatch($content->id);
    }
}
