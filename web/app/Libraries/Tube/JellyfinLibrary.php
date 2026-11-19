<?php

declare(strict_types=1);

namespace App\Libraries\Tube;

use Exception;
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

            if (! is_array($result) || blank($result)) {
                Log::error('Items API returned empty result');

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

            if (! is_array($result) || blank($result)) {
                Log::error('Similar Items API returned empty result');

                return [];
            }

            if (blank($result['Items'])) {
                Log::error('No similar items found');

                return [];
            }

            Log::notice("Found {$result['TotalRecordCount']} similar items");

            return $result['Items'];
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return [];
        }
    }
}
