<?php

namespace App\Dtos\Boogie;

class ImportSelectedVideoItem
{
    public function __construct(
        public int $videoId,
        public string $downloadPath,
    ) {}
}
