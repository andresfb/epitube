<?php

namespace App\Services;

use App\Jobs\ImportVideoJob;
use App\Models\Content;
use App\Models\MimeType;
use Exception;
use FilesystemIterator;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ImportVideosService
{
    private int $maxFiles = 0;

    private int $scanned = 0;

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        Log::info("Videos import started at " . now()->toDateTimeString());

        $this->maxFiles = config('content.max_files');

        $files = $this->scanFiles();
        if (empty($files)) {
            throw new \RuntimeException("No files found to import");
        }

        foreach ($files as $hash => $file) {
            ImportVideoJob::dispatch([
                'hash' => $hash,
                'file' => $file,
            ])
            ->onQueue('media')
            ->delay(now()->addSeconds(15));
        }
    }

    private function scanFiles(): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                config('content.data_path'),
                FilesystemIterator::SKIP_DOTS
            )
        );

        $files = [];
        $extensions = MimeType::extensions();

        foreach ($iterator as $file) {
            if ($this->scanned >= $this->maxFiles) {
                break;
            }

            if ($file->isDir() || !$file->isFile()) {
                continue;
            }

            if (!in_array($file->getExtension(), $extensions, true)) {
                continue;
            }

            $fullFile = $file->getFileInfo()->getPathname();
            $hash = hash_file('md5', $fullFile);
            if (array_key_exists($hash, $files)) {
                continue;
            }

            if (Content::found($hash)) {
                continue;
            }

            $files[$hash] = $fullFile;
            $this->scanned++;

            echo '.';
        }

        return $files;
    }
}
