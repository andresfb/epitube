<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\ContentEditItem;
use App\Dtos\Tube\ContentItem;
use App\Factories\ContentItemFactory;
use App\Jobs\SearchableWordsFromContentJob;
use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\DB;
use JsonException;
use Throwable;

final readonly class ContentEditAction
{
    /**
     * @throws Throwable
     */
    public function handle(ContentEditItem $item): ContentItem
    {
        return DB::transaction(function () use ($item): ContentItem {
            $tags = $this->parseTags($item->tags);

            $content = Content::query()
                ->where('slug', $item->slug)
                ->firstOrFail();

            $content->title = $item->title;
            $content->category_id = $item->category_id;
            $content->active = $item->active;
            $content->updateQuietly();
            $content = $content->fresh();

            $content->syncTags($tags);

            Feed::where('slug', $item->slug)
                ->update([
                    'title' => $item->title,
                    'category_id' => $item->category_id,
                    'category' => $content->category->name,
                    'active' => $item->active,
                ]);

            CacheLibrary::clear();

            SearchableWordsFromContentJob::dispatch($content->id);

            return ContentItemFactory::withContent($content);
        });
    }

    /**
     * @throws JsonException
     */
    private function parseTags(string $tags): array
    {
        return collect(json_decode($tags, true, 512, JSON_THROW_ON_ERROR))
            ->pluck('value')
            ->toArray();
    }
}
