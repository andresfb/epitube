<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

final class PreviewItem
{
    public function __construct(
        public int $contentId,
        public int $mediaId,
        public int $size,
        public int $bitRate,
        public string $extension,
        public array $sections,
    ) {}
}
