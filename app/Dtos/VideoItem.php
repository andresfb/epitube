<?php

namespace App\Dtos;

use Spatie\LaravelData\Data;

class VideoItem extends Data
{
    public function __construct(
        public string $Id,
        public string $Name,
        public string $Path,
        public int $RunTimeTicks = 0,
        public int $Width = 0,
        public int $Height = 0,
    ) {}
}
