<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

final class ContentItem extends Data
{
    /**
     * @param  array<string>  $tags
     * @param  array<VideoItem>  $videos
     * @param  array<VideoItem>  $previews
     * @param  array<ThumbnailItem>  $thumbnails
     * @param  array<int>  $related
     */
    public function __construct(
        public int $id,
        public string $slug,
        public int $category_id,
        public string $category,
        public string $title,
        public string $duration,
        public string $resolution,
        public int $length,
        public bool $is_hd,
        public bool $active,
        public bool $viewed,
        public int $like_status,
        public int $view_count,
        public bool $featured,
        public string $service_url,
        public Carbon $added_at,
        public Carbon $created_at,
        public string $tag_list = '',
        public array $tags = [],
        public array $tag_slugs = [],
        public array $tag_array = [],
        public array $videos = [],
        public array $previews = [],
        public array $thumbnails = [],
        public array $related = [],
    ) {}
}
