<?php

namespace App\Services\Tube;

use App\Traits\DirectoryChecker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ClearDirectoryDiskService
{
    use DirectoryChecker;

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
}
