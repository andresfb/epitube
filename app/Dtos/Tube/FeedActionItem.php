<?php

namespace App\Dtos\Tube;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class FeedActionItem
{
    public function __construct(
        public ?Collection $feed = null,
        public ?Htmlable $links = null,
    ) {}
}
