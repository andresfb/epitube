<?php

namespace App\Dtos;

use Spatie\LaravelData\Data;

class VideoInfoItem extends Data
{
    public function __construct(
        public bool $status,
        public int $width = 0,
        public int $height = 0,
        public int $duration = 0,
    ) {}
}
