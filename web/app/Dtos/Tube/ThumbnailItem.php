<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class ThumbnailItem extends Data
{
    public function __construct(
        public array $urls,
        public string $srcset,
    ) {}
}
