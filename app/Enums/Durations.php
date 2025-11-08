<?php

namespace App\Enums;

use function PHPUnit\Framework\matches;

enum Durations: string
{
    case QUICK = 'quick';
    case SHORT = 'short';
    case MEDIUM = 'medium';
    case LONG = 'long';
    case FEATURE = 'feature';

    public static function list(self $duration): array
    {
        return match ($duration) {
            self::QUICK => [60, 180],
            self::SHORT => [181, 600],
            self::MEDIUM => [601, 1800],
            self::LONG => [1801, 3600],
            default => [3601, 999999],
        };
    }

    public static function description(self $duration): string
    {
        return match ($duration) {
            self::QUICK => 'Quick',
            self::SHORT => 'Short',
            self::MEDIUM => 'Medium',
            self::LONG => 'Long',
            default => 'Feature Length',
        };
    }
}
