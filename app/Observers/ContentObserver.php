<?php

namespace App\Observers;

use App\Jobs\Tube\SyncFeedJob;
use App\Models\Tube\Content;
use Illuminate\Support\Facades\Cache;

class ContentObserver
{
    public function saved(Content $content): void
    {
        $savingKey = md5("CONTENT:SAVING:$content->id");
        if (Cache::has($savingKey)) {
            return;
        }

        SyncFeedJob::dispatch($content->id);
        Cache::put($savingKey, $content->id, now()->addSeconds(10));
    }
}
