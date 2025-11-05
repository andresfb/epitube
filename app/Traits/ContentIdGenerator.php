<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Tube\Content;
use Exception;
use RuntimeException;

trait ContentIdGenerator
{
    /**
     * @throws Exception
     */
    private static function generateUniqueId(): string
    {
        $maxAttempts = 10;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $slug = self::generateId();

            if (! Content::where('slug', $slug)->exists()) {
                return $slug;
            }
        }

        throw new RuntimeException("Failed to generate unique Content ID after $maxAttempts attempts");
    }

    /**
     * @throws Exception
     */
    private static function generateId(int $length = 11): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $id = '';
        for ($j = 0; $j < $length; $j++) {
            $id .= $characters[random_int(0, mb_strlen($characters) - 1)];
        }

        return $id;
    }
}
