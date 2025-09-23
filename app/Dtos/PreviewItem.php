<?php

namespace App\Dtos;

use Spatie\LaravelData\Data;

class PreviewItem extends Data
{
    public function __construct(
        public string $fulUrl,
        public int $size,
        public string $extension,
    ) {}
}
