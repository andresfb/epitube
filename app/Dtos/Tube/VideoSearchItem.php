<?php

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

class VideoSearchItem extends Data
{
    public function __construct(
        public string $term,
        public bool $viewed = false,
        public int $liked = 0,
    ) {}
}
