<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

final class FeedListItem
{
    public function __construct(
        public ?Collection $feed = null,
        public ?Htmlable $links = null,
    ) {}
}
