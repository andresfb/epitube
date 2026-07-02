<?php

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

class TagSearchItem extends Data
{
    public function __construct(
        public string $term,
    ) {}
}
