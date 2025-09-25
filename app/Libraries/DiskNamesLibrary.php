<?php

namespace App\Libraries;

class DiskNamesLibrary
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
