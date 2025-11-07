<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\ContentItem;
use App\Models\Tube\Content;

final readonly class ContentGetAction
{
    public function handle(string $slug): ContentItem
    {
        $content = Content::query()
            ->where('slug', $slug)
            ->firstOrFail();

        return ContentItem::withContent($content);
    }
}
