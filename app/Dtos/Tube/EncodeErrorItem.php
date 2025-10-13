<?php

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class EncodeErrorItem extends Data
{
    public function __construct(
        public string $content_id,
        public int $media_id,
        public string $caller,
        public string $og_path,
        public string $error,
    ) {}
}
