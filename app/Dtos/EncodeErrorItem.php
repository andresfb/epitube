<?php

namespace App\Dtos;

use Spatie\LaravelData\Data;

class EncodeErrorItem extends Data
{
    public function __construct(
        public string $content_id,
        public int $media_id,
        public string $caller,
        public string $og_path,
        public string $error,
    ) {}
}
