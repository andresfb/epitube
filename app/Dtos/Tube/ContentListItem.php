<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class ContentListItem extends Data
{
    public function __construct(
        public ?bool $active = null,
        public ?bool $viewed = null,
        public ?int $category_id = null,
        public ?int $like_status = null,
        public ?int $page = null,
        public ?string $title = null,
        public ?string $tag = null,
        public ?string $search = null,
        public ?string $sort = null,
        public ?CarbonInterface $added_after = null,
        public ?CarbonInterface $added_before = null,
        public ?CarbonInterface $created_after = null,
        public ?CarbonInterface $created_before = null,
    ) {}

    public function toArray(): array
    {
        $filter = array_filter(parent::toArray());
        unset($filter['page'], $filter['sort']);

        return array_filter([
            'filter' => $filter,
            'page' => $this->page,
            'sort' => $this->sort,
        ]);
    }

    public function isEmpty(): bool
    {
        return $this->active === null
            && $this->viewed === null
            && $this->category_id === null
            && $this->like_status === null
            && $this->page === null
            && $this->title === null
            && $this->tag === null
            && $this->search === null
            && $this->sort === null
            && ! $this->added_after instanceof CarbonInterface
            && ! $this->added_before instanceof CarbonInterface
            && ! $this->created_after instanceof CarbonInterface
            && ! $this->created_before instanceof CarbonInterface;
    }
}
