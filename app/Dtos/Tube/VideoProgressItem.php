<?php

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

class VideoProgressItem extends Data
{
    public function __construct(
        public float $current_time,
        public float $duration,
        public bool $completed,
    ) {}
}
