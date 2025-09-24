<?php

declare(strict_types=1);

namespace App\Dtos;

use Spatie\LaravelData\Data;

final class PreviewItem extends Data
{
    public function __construct(
        public string $fulUrl,
        public int $size,
        public string $extension,
    ) {}
}
