<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\ContentEditItem;
use App\Dtos\Tube\ContentItem;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ContentEditAction
{
    /**
     * @throws Throwable
     */
    public function handle(ContentEditItem $item): ContentItem
    {
        return DB::transaction(static function () use ($item): ContentItem {
            $content = Content::query()
                ->where('slug', $item->slug)
                ->firstOrFail();

            $content->title = $item->title;
            $content->category_id = $item->category_id;
            $content->active = $item->active;
            $content->updateQuietly();
            $content = $content->fresh();

            Feed::where('slug', $item->slug)
                ->update([
                    'title' => $item->title,
                    'category_id' => $item->category_id,
                    'category' => $content->category->name,
                    'active' => $item->active,
                ]);

            Cache::tags('feed')->flush();

            return ContentItem::withContent($content);
        });
    }
}
