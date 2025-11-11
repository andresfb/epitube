<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class FeedItem extends Data
{
    public function __construct(
        public int $id,
        public string $slug,
        public int $category_id,
        public string $category,
        public string $title,
        public bool $active,
        public bool $viewed,
        public bool $featured,
        public int $like_status,
        public int $view_count,
        public string $thumbnail,
        public string $duration,
        public string $resolution,
        public bool $is_hd,
        public string $added_at,
        public array $tags = [],
        public array $videos = [],
        public array $previews = [],
        public array $related = [],
    ) {}
}
