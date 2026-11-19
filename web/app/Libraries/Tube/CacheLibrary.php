<?php

declare(strict_types=1);

namespace App\Libraries\Tube;

use Illuminate\Support\Facades\Cache;

final class CacheLibrary
{
    public static function clear(array $tags = ['feed', 'tags']): void
    {
        Cache::tags($tags)->flush();
    }
}
