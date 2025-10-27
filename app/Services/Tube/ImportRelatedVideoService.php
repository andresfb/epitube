<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Libraries\Tube\JellyfinLibrary;
use App\Models\Tube\Content;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class ImportRelatedVideoService
{
    public function execute(int $contentId): void
    {
        Log::notice("Starting Related Videos process for Content Id: $contentId");

        $key = md5(Config::string('content.related_checks_key'));
        $checkedList = array_map('intval', Cache::get($key, []));

        $content = Content::query()
            ->where('id', $contentId)
            ->firstOrFail();

        Log::notice('Looking for related videos');
        $items = JellyfinLibrary::getSimilarItems($content->item_id);

        if ($items === []) {
            Log::warning("No related videos found for Item: $content->id | Content: $content->title");
            $checkedList[] = $content->id;
            $this->saveChecked($checkedList);

            return;
        }

        $imported = 0;
        $maxCount = Config::integer('content.max_related_videos') * 3;
        if (count($items) < $maxCount) {
            $maxCount = count($items);
        }

        foreach (collect($items)->random($maxCount) as $item) {
            $relatedContent = Content::query()
                ->without(['category', 'tags', 'media', 'related'])
                ->where('item_id', $item['Id'])
                ->where('active', true)
                ->first();

            if ($relatedContent === null) {
                continue;
            }

            if ($relatedContent->id === $contentId) {
                $checkedList[] = $contentId;

                continue;
            }

            if ($relatedContent->category_id !== $content->category_id) {
                $checkedList[] = $contentId;

                continue;
            }

            $content->related()->syncWithoutDetaching($relatedContent->id);
            $relatedContent->related()->syncWithoutDetaching($content->id);
            $relatedContent->touch();

            $imported++;
        }

        $this->saveChecked($checkedList);
        $content->touch();

        Log::notice("Imported $imported out of $maxCount related videos for Content Id: $contentId");
    }

    private function saveChecked(array $checkedList): void
    {
        $key = md5(Config::string('content.related_checks_key'));
        $extras = array_map('intval', Cache::get($key, []));
        $final = array_unique(array_merge($checkedList, $extras));

        Cache::put($key, $final, now()->addWeek());
    }
}
