<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\Media;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Filesystem;

final class MasterVideoLibrary
{
    private int $contentId = 0;

    private int $duration = 0;

    private int $height = 0;

    private string $processingDisk = 'processing';

    private string $downloadDisk = 'download';

    private string $tempPath = '';

    private string $relativeVideoPath = '';

    private string $processingPath = '';

    private string $downloadPath = '';

    public function getContentId(): int
    {
        return $this->contentId;
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

    public function getDownloadPath(): string
    {
        return $this->downloadPath;
    }

    public function getTempPath(): string
    {
        return $this->tempPath;
    }

    public function getRelativeVideoPath(): string
    {
        return $this->relativeVideoPath;
    }

    public function __construct(private readonly Filesystem $filesystem) {}

    public function prepare(int $mediaId, string $caller): void
    {
        $media = Media::where('id', $mediaId)->firstOrFail();
        $this->contentId = $media->model_id;

        $isVideo = (bool) $media->getCustomProperty('is_video', false);
        if (! $isVideo) {
            throw new RuntimeException('The media provided is not a video');
        }

        $this->height = (int) $media->getCustomProperty('height', 0);
        $this->duration = (int) $media->getCustomProperty('duration', 0);
        if ($this->duration < Config::integer('content.minimum_duration')) {
            throw new RuntimeException("The video duration is too short: $this->duration");
        }

        // create temp folder
        $this->tempPath = md5("$caller:$media->file_name");
        $this->processingPath = Storage::disk($this->processingDisk)->path($this->tempPath);
        if (! is_dir($this->processingPath) && ! mkdir($this->processingPath, 0777, true) && ! is_dir($this->processingPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->processingPath));
        }

        // prepare a local file
        $tempMasterPath = md5($media->file_name);
        $masterDownloadPath = Storage::disk($this->downloadDisk)->path($tempMasterPath);
        if (! is_dir($masterDownloadPath) && ! mkdir($masterDownloadPath, 0777, true) && ! is_dir($masterDownloadPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $masterDownloadPath));
        }

        $videoFileName = pathinfo($media->file_name, PATHINFO_BASENAME);
        $this->downloadPath = sprintf('%s%s%s', $masterDownloadPath, DIRECTORY_SEPARATOR, $videoFileName);
        $this->relativeVideoPath = sprintf('%s%s%s', $tempMasterPath, DIRECTORY_SEPARATOR, $videoFileName);

        if (! File::exists($this->downloadPath)) {
            // download the video from S3
            Log::notice("Downloading video file: $this->downloadPath");
            $this->filesystem->copyFromMediaLibrary($media, $this->downloadPath);
        }

        // Load the video into FFMpeg and check it.
        $video = FFMpeg::fromDisk($this->downloadDisk)->open($this->relativeVideoPath);
        if (! $video->isVideo()) {
            throw new RuntimeException('The media is not a valid video');
        }

        // Remove the video reference from memory.
        unset($video);
    }

    public function deleteTempFiles(): void
    {
        File::deleteDirectory($this->processingPath);
    }
}
