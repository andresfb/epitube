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

    private static array $extensionList = [];

    public static function list(): array
    {
        if (!empty(self::$mimeList)) {
            return self::$mimeList;
        }

        self::$mimeList = Cache::tags('list-of-mime-types')
            ->remember(
                md5(__CLASS__.__FUNCTION__),
                now()->addMinutes(30),
                static function () {
                    return self::select('type')
                        ->pluck('type')
                        ->toArray();
                });

        return self::$mimeList;
    }

    public static function extensions(): array
    {
        if (!empty(self::$extensionList)) {
            return self::$extensionList;
        }

        self::$extensionList = Cache::tags('list-of-mime-extensions')
            ->remember(
                md5(__CLASS__.__FUNCTION__),
                now()->addMinutes(30),
                static function () {
                    return self::where('extension', '!=', '*')
                        ->pluck('extension')
                        ->toArray();
                }
            );

        return self::$extensionList;
    }
}
