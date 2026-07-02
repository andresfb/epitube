<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\TagListItem;
use App\Dtos\Tube\TagSearchItem;
use App\Models\Tube\Tag;
use Illuminate\Support\Collection;

final readonly class TagSearchAction
{
    /**
     * @return Collection<TagListItem>
     */
    public function handle(TagSearchItem $item): Collection
    {
        return Tag::search($item->term)
            ->map(fn(array $tag) => TagListItem::from($tag));
    }
}
