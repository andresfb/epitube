<?php

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

class TagListItem extends Data
{
    public function __construct(
        public string $slug,
        public string $name,
        public int $count,
    ) {}
}
