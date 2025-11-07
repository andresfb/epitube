<?php

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

class ContentEditItem extends Data
{
    public function __construct(
        public string $slug,
        public string $title,
        public int $category_id,
        public bool $active,
    ) {}
}
