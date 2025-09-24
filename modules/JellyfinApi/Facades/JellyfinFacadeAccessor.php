<?php

declare(strict_types=1);

namespace Modules\JellyfinApi\Facades;

use Exception;
use Modules\JellyfinApi\Services\JellyfinService as JellyfinClient;

final class JellyfinFacadeAccessor
{
    public static JellyfinClient $provider;

    /**
     * @throws Exception
     */
    public static function getProvider(): JellyfinClient
    {
        return self::$provider;
    }

    /**
     * @throws Exception
     */
    public static function setProvider(): JellyfinClient
    {
        // Set default provider.
        self::$provider = new JellyfinClient();

        return self::getProvider();
    }
}
