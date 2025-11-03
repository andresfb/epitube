<?php

namespace App\Libraries\Tube;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\JellyfinApi\Facades\Jellyfin;

final readonly class JellyfinLibrary
{
    public static function getItems(): array
    {
        Log::notice('Calling the Service API');

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

            Log::notice("Found {$result['TotalRecordCount']} items");

            return $result['Items'];
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return [];
        }
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

            if (! is_array($result) || blank($result['Items'])) {
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
