<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Contracts\FeedMirror;
use App\Dtos\Tube\ContentEditItem;
use App\Dtos\Tube\ContentItem;
use App\Factories\ContentItemFactory;
use App\Jobs\Tube\SearchableWordsFromContentJob;
use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\Content;
use Illuminate\Support\Facades\DB;
use JsonException;
use Throwable;

final readonly class ContentEditAction
{
    public function __construct(private FeedMirror $feedMirror) {}

    /**
     * @throws Throwable
     */
    public function handle(ContentEditItem $item): ContentItem
    {
        return DB::transaction(function () use ($item): ContentItem {
            $tags = $this->parseTags($item->tags);

            $content = Content::query()
                ->with('tags')
                ->where('slug', $item->slug)
                ->firstOrFail();

            $content->title = $item->title;
            $content->category_id = $item->category_id;
            $content->active = $item->active;
            $content->updateQuietly();

            $content = $content->fresh();
            $content->syncTags($tags);
            $content = $content->fresh();

            $this->feedMirror->updateBySlug($content->slug, [
                'title' => $item->title,
                'category_id' => $item->category_id,
                'category' => $content->category->name,
                'active' => $item->active,
                'tags' => $content->tags->pluck('name')->toArray(),
                'tag_slugs' => $content->tags->pluck('slug')->toArray(),
                'tag_array' => $content->tags->pluck('name', 'slug')->toArray(),
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
