<?php

declare(strict_types=1);

namespace App\Libraries\Tube;

final class DiskNamesLibrary
{
    public static function content(): string
    {
        return 'content';
    }

    public static function transcode(): string
    {
        return 'transcode';
    }

    public static function processing(): string
    {
        return 'processing';
    }

    public static function download(): string
    {
        return 'download';
    }

    public static function media(): string
    {
        return config('media-library.disk_name');
    }
}
