<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class WordResultItem extends Data
{
    public function __construct(
        public string $word,
    ) {}
}
