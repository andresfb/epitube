<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class ContentEditItem extends Data
{
    public function __construct(
        public string $slug,
        public string $title,
        public int $category_id,
        public string $tags,
        public bool $active,
    ) {}
}
