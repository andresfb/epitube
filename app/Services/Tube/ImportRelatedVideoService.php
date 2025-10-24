<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Libraries\Tube\JellyfinLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\RelatedContent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class ImportRelatedVideoService
{
    public bool $toScreen = false;

    public function execute(int $contentId): void
    {
        Log::notice("Starting Related Videos process for Content Id: $contentId");

        $key = md5(Config::string('content.related_checks_key'));
        $checkedList = array_map('intval', Cache::get($key, []));

        $content = Content::query()
            ->where('id', $contentId)
            ->firstOrFail();

        Log::notice('Looking for related videos');
        $items =JellyfinLibrary::getSimilarItems($content->item_id);

        if ($items === []) {
            Log::warning("No related videos found for Item: $content->id | Content: $content->title");
            $checkedList[] = $content->id;
            $this->saveChecked($checkedList);

            return;
        }

        $imported = 0;
        $maxCount = Config::integer('content.max_related_videos') * 3;
        $list = collect($items)->random($maxCount)->toArray();

        foreach ($list as $item) {
            $relatedContent = Content::query()
                ->without(['category', 'tags', 'media', 'related'])
                ->where('item_id', $item['Id'])
                ->where('active', true)
                ->first();

            if ($relatedContent === null) {
                if ($this->toScreen) {
                    echo 'x';
                }

                continue;
            }

            if ($this->toScreen) {
                echo 'f';
            }

            if ($relatedContent->id === $contentId) {
                $checkedList[] = $contentId;

                if ($this->toScreen) {
                    echo '=';
                }

                continue;
            }

            if ($relatedContent->category_id !== $content->category_id) {
                $checkedList[] = $contentId;

                if ($this->toScreen) {
                    echo 'o';
                }

                continue;
            }

            // Add the relationship
            RelatedContent::updateOrCreate([
                'content_id' => $contentId,
                'related_content_id' => $relatedContent->id,
            ]);

            // Add the corresponding relationship
            RelatedContent::updateOrCreate([
                'content_id' => $relatedContent->id,
                'related_content_id' => $contentId,
            ]);

            $imported++;
            if ($this->toScreen) {
                echo '.';
            }
        }

        if ($this->toScreen) {
            echo PHP_EOL;
        }

        Log::notice("Imported $imported out of $maxCount related videos");

        $content->touch();
        $this->saveChecked($checkedList);
        Log::notice("Done Related Videos process for Content Id: $contentId");
    }

    private function saveChecked(array $checkedList): void
    {
        $key = md5(Config::string('content.related_checks_key'));
        $extras = array_map('intval', Cache::get($key, []));
        $final = array_unique(array_merge($checkedList, $extras));

        Cache::put($key, $final, now()->addWeek());
    }
}
