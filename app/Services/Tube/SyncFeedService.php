<?php

namespace App\Services\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Feed;

class SyncFeedService
{
    public function execute(int $contentId): void
    {
        $content = Content::query()
            ->where('id', $contentId)
            ->firstOrFail();

        Feed::generate($content);
    }
}
