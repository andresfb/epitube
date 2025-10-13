<?php

declare(strict_types=1);

namespace App\Libraries\Tube;

final readonly class MediaNamesLibrary
{
    public static function videos(): string
    {
        return 'videos';
    }

    public static function transcoded(): string
    {
        return 'transcoded';
    }

    public static function downscaled(): string
    {
        return 'downscaled';
    }

    public static function previews(): string
    {
        return 'previews';
    }

    public static function thumbnails(): string
    {
        return 'thumbnails';
    }

    public static function hlsConversion(): string
    {
        return 'hls';
    }
}
