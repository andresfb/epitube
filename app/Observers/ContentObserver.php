<?php

namespace App\Observers;

use App\Jobs\Tube\SyncFeedJob;
use App\Models\Tube\Content;

class ContentObserver
{
    public function saved(Content $content): void
    {
        SyncFeedJob::dispatch($content->id);
    }
}
