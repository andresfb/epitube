<?php

declare(strict_types=1);

namespace App\Traits;

trait LanguageChecker
{
    private function containsNonLatin(string $text): bool
    {
        // Return true if any character is outside the Basic Latin block
        return preg_match('/[^\x00-\x7F]/u', $text) === 1;
    }
}
