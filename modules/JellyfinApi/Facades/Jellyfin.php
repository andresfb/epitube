<?php

namespace Modules\JellyfinApi\Facades;

use Illuminate\Support\Facades\Facade;

class Jellyfin extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return JellyfinFacadeAccessor::class;
    }
}
