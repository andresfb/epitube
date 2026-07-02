<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

class CategoryItem extends Data
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
    ) {}
}
