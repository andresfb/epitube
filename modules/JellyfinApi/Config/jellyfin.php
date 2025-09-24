<?php

declare(strict_types=1);
/**
 * Jellyfin Setting & API Credentials
 * Created by Thomas <me@hvs.cx>.
 */
$serverUrl = env('JELLYFIN_SERVER_URL', '');
$serverId = env('JELLYFIN_SERVER_ID', '');

return [
    'server_url' => $serverUrl,
    'token' => env('JELLYFIN_TOKEN', ''),

    'application' => env('JELLYFIN_APPLICATION', 'Laravel Jellyfin / v1.0'), // Jellyfin application name
    'version' => env('JELLYFIN_VERSION', '10.10.07'), // (Jellyfin application version number)

    'validate_ssl' => env('JELLYFIN_VALIDATE_SSL', true), // Validate SSL when creating api client.

    'server_id' => $serverId,

    'item_web_url' => "$serverUrl/web/#/details?id=%s&serverId=$serverId",

];
