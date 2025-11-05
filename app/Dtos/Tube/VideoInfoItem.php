<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class VideoInfoItem extends Data
{
    public function __construct(
        public bool $status,
        public int $width = 0,
        public int $height = 0,
        public int $duration = 0,
    ) {}
}
