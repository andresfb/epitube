<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\JellyfinLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\RelatedContent;
use Illuminate\Support\Facades\Log;

final class ImportRelatedVideoService
{
    public function execute(int $contentId): void
    {
        $content = Content::where('id', $contentId)
            ->firstOrFail();

        $items =JellyfinLibrary::getSimilarItems($content->item_id);
        if ($items === []) {
            Log::error("No related videos found for $contentId");

            return;
        }

        foreach ($items as $item) {
            $relatedContent = Content::select('id')
                ->withoutRelations()
                ->where('item_id', $item['Id'])
                ->first();

            if ($relatedContent === null) {
                continue;
            }

            if ($relatedContent->id === $contentId) {
                continue;
            }

            RelatedContent::updateOrCreate([
                'content_id' => $contentId,
                'related_content_id' => $relatedContent->id,
            ]);
        }
    }
}
