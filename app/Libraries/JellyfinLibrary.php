<?php

namespace App\Libraries;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\JellyfinApi\Facades\Jellyfin;

final readonly class JellyfinLibrary
{
    public static function getItems(): array
    {
        Log::notice('Calling the Service API');

        $result = Cache::remember(
            'VIDEOS:FROM:API',
            now()->addDay()->subSeconds(2),
            static function (): array {
                try {
                    Jellyfin::setProvider();
                    $provider = Jellyfin::getProvider();
                    $result = $provider->getItems();

                    if (blank($result)) {
                        Log::error('Api returned empty array');

                        return [];
                    }

                    if (blank($result['Items'])) {
                        Log::error('No items found');

                        return [];
                    }

                    return $result;
                } catch (Exception $e) {
                    Log::error($e->getMessage());

                    return [];
                }
            }
        );

        Log::notice("Found {$result['TotalRecordCount']} items");

        return $result['Items'];
    }

    public static function getSimilarItems(string $itemId): array
    {
        try {
            Jellyfin::setProvider();
            $provider = Jellyfin::getProvider();
            $result = $provider->getSimilarItems($itemId);

            if (blank($result)) {
                Log::error('Api returned empty array');

                return [];
            }

            if (blank($result['Items'])) {
                Log::error('No items found');

                return [];
            }

            Log::notice("Found {$result['TotalRecordCount']} items");

            return $result['Items'];
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return [];
        }
    }
}
