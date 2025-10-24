<?php

namespace App\Services\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Log;

class SyncFeedService
{
    public function execute(int $contentId): void
    {
        $content = Content::query()
            ->with('related')
            ->where('id', $contentId)
            ->first();

        if ($content === null) {
            Log::error("No content found for $contentId");

            return;
        }

        Feed::generate($content);
    }
}
