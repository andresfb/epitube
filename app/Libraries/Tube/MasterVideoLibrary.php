<?php

declare(strict_types=1);

namespace App\Libraries\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Media;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Filesystem;

final class MasterVideoLibrary
{
    private int $duration = 0;

    private int $height = 0;

    private string $processingDisk;

    private string $downloadDisk;

    private string $tempPath = '';

    private string $relativeVideoPath = '';

    private string $processingPath = '';

    private string $masterFile = '';

    private Media $media;

    private Content $content;

    public function __construct(private readonly Filesystem $filesystem)
    {
        $this->processingDisk = DiskNamesLibrary::processing();
        $this->downloadDisk = DiskNamesLibrary::download();
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getProcessingDisk(): string
    {
        return $this->processingDisk;
    }

    public function getDownloadDisk(): string
    {
        return $this->downloadDisk;
    }

    public function getProcessingPath(): string
    {
        return $this->processingPath;
    }

    public function getMasterFile(): string
    {
        return $this->masterFile;
    }

    public function getTempPath(): string
    {
        return $this->tempPath;
    }

    public function getRelativeVideoPath(): string
    {
        return $this->relativeVideoPath;
    }

    public function getMedia(): Media
    {
        return $this->media;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function prepare(int $mediaId, string $caller): void
    {
        $this->media = Media::where('id', $mediaId)
            ->firstOrFail();

        $this->content = Content::where('id', $this->media->model_id)
            ->firstOrFail();

        if (! $this->media->getCustomProperty('is_video', false)) {
            throw new RuntimeException('The media provided is not a video');
        }

        $this->downloadMaster($this->media);

        // create temp folder
        $this->tempPath = md5("$caller:{$this->media->file_name}");
        $this->processingPath = Storage::disk($this->processingDisk)->path($this->tempPath);
        if (! is_dir($this->processingPath) && ! mkdir($this->processingPath, 0777, true) && ! is_dir($this->processingPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->processingPath));
        }

        $this->height = (int) $this->media->getCustomProperty('height', 0);
        $this->duration = (int) $this->media->getCustomProperty('duration', 0);
        if ($this->duration < Config::integer('content.minimum_duration')) {
            throw new RuntimeException("The video duration is too short: $this->duration");
        }
    }

    public function downloadMaster(Media $media): void
    {
        if (! $media->getCustomProperty('transcoded', false)) {
            $this->downloadDisk = DiskNamesLibrary::content();
            $this->masterFile = $media->getPath();
            $this->relativeVideoPath = $media->getPathRelativeToRoot();

            return;
        }

        $this->prepareDownloadPath($media);

        if (! File::exists($this->masterFile)) {
            // download the video from S3
            Log::notice("Downloading video file: $this->masterFile");
            $this->filesystem->copyFromMediaLibrary($media, $this->masterFile);
        }
    }

    public function prepareDownloadPath(Media $media): void
    {
        $this->downloadDisk = DiskNamesLibrary::download();

        // prepare a local file
        $tempMasterPath = md5($media->file_name);
        $masterDownloadPath = Storage::disk($this->downloadDisk)->path($tempMasterPath);
        if (! is_dir($masterDownloadPath) && ! mkdir($masterDownloadPath, 0777, true) && ! is_dir($masterDownloadPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $masterDownloadPath));
        }

        $videoFileName = pathinfo($media->file_name, PATHINFO_BASENAME);
        $this->masterFile = sprintf('%s%s%s', $masterDownloadPath, DIRECTORY_SEPARATOR, $videoFileName);
        $this->relativeVideoPath = sprintf('%s%s%s', $tempMasterPath, DIRECTORY_SEPARATOR, $videoFileName);
    }

    public function deleteTempFiles(): void
    {
        File::deleteDirectory($this->processingPath);
    }
}
