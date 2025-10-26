<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class VideoItem extends Data
{
    public function __construct(
        public string $url,
        public string $mimeType,
        public int $duration = 0,
        public int $height = 0,
    ) {}
}
