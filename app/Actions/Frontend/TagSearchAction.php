<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\TagListItem;
use App\Models\Tube\Tag;
use Illuminate\Support\Collection;

final readonly class TagSearchAction
{
    /**
     * @return Collection<TagListItem>
     */
    public function handle(string $term): Collection
    {
        return Tag::search($term)
            ->map(fn(array $tag) => TagListItem::from($tag));
    }
}
