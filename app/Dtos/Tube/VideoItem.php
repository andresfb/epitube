<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class VideoItem extends Data
{
    public function __construct(
        public string $fulUrl,
        public int $duration,
        public int $width,
        public int $height,
    ) {}
}
