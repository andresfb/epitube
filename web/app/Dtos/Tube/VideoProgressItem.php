<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class VideoProgressItem extends Data
{
    public function __construct(
        public float $current_time,
        public float $duration,
        public bool $completed,
    ) {}
}
