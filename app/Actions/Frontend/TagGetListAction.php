<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\TagListItem;
use App\Models\Tube\Tag;
use Illuminate\Support\Collection;

final readonly class TagGetListAction
{
    /**
     * @return Collection<TagListItem>
     */
    public function handle(): Collection
    {
        return Tag::getListWithCount()
            ->map(fn (array $tag) => TagListItem::from($tag));
    }
}
