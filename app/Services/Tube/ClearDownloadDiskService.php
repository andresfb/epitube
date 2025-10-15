<?php

namespace App\Services\Tube;

use App\Libraries\Tube\DiskNamesLibrary;
use App\Traits\DirectoryChecker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\SplFileInfo;

class ClearDownloadDiskService
{
    use DirectoryChecker;

    private array $directories = [];

    public function execute(): void
    {
        try {
            $path = Storage::disk(DiskNamesLibrary::download())->path('');
            $files = File::allFiles($path);

            if (blank($files)) {
                Log::notice('No files found to delete');

                return;
            }

            collect($files)->each(function (SplFileInfo $file) {
                Log::notice("Preparing to delete {$file->getPathname()}");

                $mTime = (int)$file->getMTime();
                if ($mTime >= now()->subDay()->timestamp) {
                    Log::warning("Not yet: " . date('Y-m-d H:i:s', $mTime));

                    return;
                }

                $this->directories[] = $file->getRelativePath();
                File::delete($file->getRealPath());
                Log::notice('Deleted');
            });

            if (blank($this->directories)) {
                Log::warning('No directories found to delete');

                return;
            }

            foreach ($this->directories as $directory) {
                Log::notice("Deleting {$directory}");

                $path = Storage::disk(DiskNamesLibrary::download())->path($directory);
                File::deleteDirectories($path);
                File::deleteDirectory($path);

                Log::notice('Deleted');
            }
        } finally {
            $this->clearSelectedVideoDownload();
        }
    }

    private function clearSelectedVideoDownload(): void
    {
        $path = Config::string('selected-videos.download_path');
        $directories = File::directories($path);

        foreach ($directories as $directory) {
            if (! $this->isDirectoryEmpty($directory)) {
                return;
            }

            File::deleteDirectory($directory);
        }
    }
}
