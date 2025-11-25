<?php

namespace App\Services\Tube;

use App\Dtos\Tube\ImportVideoItem;
use App\Jobs\Tube\ImportVideoJob;
use App\Models\ExtraVideoPath;
use App\Models\Tube\Content;
use App\Models\Tube\MimeType;
use App\Models\Tube\Rejected;
use App\Traits\ImportItemCreator;
use App\Traits\Screenable;
use App\Traits\VideoValidator;
use FilesystemIterator;
use Illuminate\Support\Facades\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ImportVideosPathsService
{
    use ImportItemCreator;
    use VideoValidator;
    use Screenable;

    private int $maxFiles;

    private int $scanned = 0;

    public function __construct(private readonly ImportVideoService $videoService)
    {
        $this->maxFiles = (int) floor(Config::integer('content.max_import_videos') / 2);
    }

    public function execute(): void
    {
        $this->notice('Extra Videos Paths import started at '.now()->toDateTimeString());

        $paths = ExtraVideoPath::getActive();
        if (empty($paths)) {
            $this->warning('No Extra Videos Paths found');

            return;
        }

        shuffle($paths);
        foreach ($paths as $path) {
            if ($this->scanned >= $this->maxFiles) {
                $this->warning("Maximum number of imports reached");

                break;
            }

            $this->importPath($path);
        }

        $this->notice('Extra Videos Paths import ended at '.now()->toDateTimeString());
    }

    private function importPath(string $path): void
    {
        $baseDir = sprintf('%s/%s', Config::string('content.data_path'), $path);
        if (! file_exists($baseDir)) {
            $this->warning("Extra Videos Paths not found: $baseDir");

            return;
        }

        $files = $this->scanPath($baseDir);
        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            if ($this->scanned >= $this->maxFiles) {
                break;
            }

            $item = new ImportVideoItem(
                Id: md5($file),
                Name: pathinfo($file, PATHINFO_FILENAME),
                Path: $file,
                MimeType: mime_content_type($file),
            );

            if ($this->wasImported($item)) {
                continue;
            }

            if ($this->wasRejected($item)) {
                continue;
            }

            $this->notice("Dispatching Video Import for $item->Path");
            ImportVideoJob::dispatch($item);

            $this->scanned++;
        }
    }

    /**
     * @return array<string>
     */
    private function scanPath(string $baseDir): array
    {
        $this->notice("Scanning directory $baseDir");

        $videos = [];
        $extensions = MimeType::extensions();

        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
        );

        $scanned = 0;
        $maxScans = $this->maxFiles * 10;

        foreach ($iterator as $fileInfo) {
            if ($scanned >= $maxScans) {
                break;
            }

            if (! $fileInfo->isFile()) {
                continue;
            }

            $ext = strtolower($fileInfo->getExtension());
            if (! in_array($ext, $extensions, true)) {
                continue;
            }

            $videos[] = $fileInfo->getRealPath();
            $scanned++;
        }

        shuffle($videos);
        $count = count($videos);

        if ($count > 0) {
            $this->notice("Found $count videos on $baseDir");
        }

        return $videos;
    }

    private function wasImported(ImportVideoItem $importItem): bool
    {
        $content = Content::query()
            ->where('item_id', $importItem->Id)
            ->orWhere('og_path', $importItem->Path)
            ->first();

        return $content !== null;
    }

    private function wasRejected(ImportVideoItem $importItem): bool
    {
        $rejected = Rejected::query()
            ->where('item_id', $importItem->Id)
            ->orWhere('og_path', $importItem->Path)
            ->first();

        return $rejected !== null;
    }
}
