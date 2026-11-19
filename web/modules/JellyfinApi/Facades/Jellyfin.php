<?php

declare(strict_types=1);

namespace Modules\JellyfinApi\Facades;

use Illuminate\Support\Facades\Facade;

final class Jellyfin extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return JellyfinFacadeAccessor::class;
    }
}
