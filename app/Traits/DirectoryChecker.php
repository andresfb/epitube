<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait DirectoryChecker
{
    private function isDirectoryEmpty(string $dir): bool
    {
        // Ensure the path is a directory
        if (! is_dir($dir)) {
            Log::warning("Item '$dir' is not a directory");

            return false;
        }

        // Ensure the path isis readable
        if (! is_readable($dir)) {
            Log::warning("Item '$dir' is not readable");

            return false;
        }

        // Scan the directory
        $files = scandir($dir);

        // Remove '.' and '..' from the results
        $files = array_diff($files, ['.', '..']);

        // If the remaining array is empty, the directory has no contents
        return count($files) === 0;
    }

    private function isHash(string $value): bool
    {
        $value = mb_trim($value);

        // Hex‑only hashes (MD5, SHA‑1, SHA‑256, SHA‑512)
        $hexLengths = [32, 40, 64, 128];
        if (ctype_xdigit($value) && in_array(strlen($value), $hexLengths, true)) {
            return true;
        }

        // bcrypt: $2a$, $2b$, or $2y$ followed by cost and 53‑char salt+hash
        if (preg_match('/^\$2[aby]\$\d{2}\$[.\/A-Za-z0-9]{53}$/', $value)) {
            return true;
        }
        // Argon2i / Argon2id
        return (bool) preg_match('/^\$(argon2i|argon2id)\$[^$]+\$[^$]+\$[A-Za-z0-9\/+.=]+$/', $value);
    }
}
