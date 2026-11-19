<?php

declare(strict_types=1);

namespace App\Dtos\Boogie;

final class ImportSelectedVideoItem
{
    public function __construct(
        public int $videoId,
        public string $downloadPath,
    ) {}
}
