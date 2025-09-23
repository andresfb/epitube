<?php

namespace App\Services;

use App\Models\Content;
use App\Models\RelatedContent;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\JellyfinApi\Facades\Jellyfin;

class ImportRelatedVideoService
{
    public function execute(int $contentId): void
    {
        $content = Content::where('id', $contentId)
            ->firstOrFail();

        $items = $this->loadFromAPI($content->item_id);
        if ($items === []) {
            Log::error("No related videos found for $contentId");

            return;
        }

        foreach ($items as $item) {
            $relatedContent = Content::select('id')
                ->withoutRelations()
                ->where('item_id', $item['Id'])
                ->first();

            if ($relatedContent === null || $relatedContent->id === $contentId) {
                continue;
            }

            RelatedContent::updateOrCreate([
                'content_id' => $contentId,
                'related_content_id' => $relatedContent->id,
            ]);
        }
    }

    private function loadFromAPI(string $itemId): array
    {
        try {
            Jellyfin::setProvider();
            $provider = Jellyfin::getProvider();
            $result = $provider->getSimilarItems($itemId);

            if (blank($result)) {
                Log::error('Api returned empty array');

                return [];
            }

            if (blank($result['Items'])) {
                Log::error("No items found");

                return [];
            }

            Log::notice("Found {$result['TotalRecordCount']} items");

            return $result['Items'];
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return [];
        }
    }
}
