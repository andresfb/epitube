<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ClearDirectoryDiskService
{
    public function execute(string $disk): void
    {
        $path = Storage::disk($disk)->path('');
        if (blank($path) || ! File::exists($path)) {
            throw new RuntimeException("Requested Disk: $disk does not exist.");
        }

        $dirs = File::directories($path);
        if (blank($dirs)) {
            Log::notice('No directories found to delete');

            return;
        }

        collect($dirs)->each(function (string $dir) {
            Log::notice("Preparing to delete $dir");

            if (! $this->isDirectoryEmpty($dir)) {
                Log::warning("Directory is not empty");

                return;
            }

            File::deleteDirectories($dir);
            File::deleteDirectory($dir);
            Log::notice('Deleted');
        });
    }

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
