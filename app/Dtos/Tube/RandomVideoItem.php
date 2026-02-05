<?php

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

class RandomVideoItem extends Data
{
    public function __construct(
        public ?int $category_id = 0,
        public ?string $tag = null,
        public int $count = 5,
    ) {}
}
