<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class WordSearchItem extends Data
{
    public function __construct(
        public string $term,
        public int $count = 10,
    ) {}
}
