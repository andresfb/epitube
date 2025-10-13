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
}
