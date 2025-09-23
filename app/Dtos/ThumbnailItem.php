<?php

namespace App\Dtos;

use Spatie\LaravelData\Data;

class ThumbnailItem extends Data
{
    public function __construct(
        public array $urls,
        public string $srcset,
    ) {}
}
