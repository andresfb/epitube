<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MimeType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'extension',
        'type',
    ];

    private static array $mimeList = [];

    private static array $hlsMimeList = [];

    private static array $transcodableMimeList = [];

    private static array $extensionList = [];

    public static function list(): array
    {
        if (! empty(self::$mimeList)) {
            return self::$mimeList;
        }

        self::$mimeList = Cache::tags('list-of-mime-types')
            ->remember(
                md5(__CLASS__.__FUNCTION__),
                now()->addMinutes(30),
                static function (): array {
                    return self::select('type')
                        ->groupBy('type')
                        ->pluck('type')
                        ->toArray();
                });

        return self::$mimeList;
    }

    public static function extensions(): array
    {
        if (! empty(self::$extensionList)) {
            return self::$extensionList;
        }

        self::$extensionList = Cache::tags('list-of-mime-extensions')
            ->remember(
                md5(__CLASS__.__FUNCTION__),
                now()->addMinutes(30),
                static function (): array {
                    return self::where('extension', '!=', '*')
                        ->groupBy('extension')
                        ->pluck('extension')
                        ->toArray();
                }
            );

        return self::$extensionList;
    }

    public static function transcode(): array
    {
        if (!empty(self::$transcodableMimeList)) {
            return self::$transcodableMimeList;
        }

        self::$transcodableMimeList = Cache::tags('transcodable-list-of-mime-types')
            ->remember(
                md5(__CLASS__.__FUNCTION__),
                now()->addMinutes(30),
                static function (): array {
                    return self::select('type')
                        ->where('transcode', true)
                        ->groupBy('type')
                        ->pluck('type')
                        ->toArray();
                });

        return self::$transcodableMimeList;
    }

    public static function canHls(): array
    {
        if (! empty(self::$hlsMimeList)) {
            return self::$hlsMimeList;
        }

        self::$hlsMimeList = Cache::tags('hls-list-of-mime-types')
            ->remember(
                md5(__CLASS__.__FUNCTION__),
                now()->addMinutes(30),
                static function (): array {
                    return self::select('type')
                        ->where('transcode', false)
                        ->groupBy('type')
                        ->pluck('type')
                        ->toArray();
                });

        return self::$hlsMimeList;
    }
}
