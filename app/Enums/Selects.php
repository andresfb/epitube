<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Facades\Config;

enum Selects: string
{
    case FEATURED = 'featured';
    case WATCHED = 'watched';
    case LIKED = 'liked';
    case DISLIKED = 'disliked';

    public static function title(self $select): string
    {
        return match ($select) {
            self::WATCHED => 'Watched',
            self::LIKED => 'Liked',
            self::DISLIKED => 'Disliked',
            default => Config::string('content.featured_title'),
        };
    }

    public static function icon(self $select): string
    {
        return match ($select) {
            self::WATCHED => '👀',
            self::LIKED => '❤️',
            self::DISLIKED => '👎',
            default => Config::string('content.featured_icon'),
        };
    }
}
