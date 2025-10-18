<?php

namespace App\Observers;

use App\Jobs\Tube\SyncFeedJob;
use App\Models\Tube\Content;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class ContentObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Content $content): void
    {
        $cacheKey = md5("CONTENT:SAVED:$content->id");
        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addSeconds(6));
        SyncFeedJob::dispatch($content->id);
    }
}
