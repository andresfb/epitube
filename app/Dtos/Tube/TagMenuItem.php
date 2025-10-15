<?php

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

class TagMenuItem extends Data
{
    public function __construct(
        public string $slug,
        public string $name,
        public int $count,
    ) {}
}
