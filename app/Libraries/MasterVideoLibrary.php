<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\Media;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use RuntimeException;

final class MasterVideoLibrary
{
    private int $contentId = 0;

    private int $duration = 0;

    private string $processingDisk = 'processing';

    private string $tempPath = '';

    private string $relativeVideoPath = '';

    private string $localFilePath = '';

    private string $processingPath = '';

    public function getContentId(): int
    {
        return $this->contentId;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getProcessingDisk(): string
    {
        return $this->processingDisk;
    }

    public function getTempPath(): string
    {
        return $this->tempPath;
    }

    public function getRelativeVideoPath(): string
    {
        return $this->relativeVideoPath;
    }

    public function getLocalFilePath(): string
    {
        return $this->localFilePath;
    }

    public function prepare(int $mediaId, string $caller): void
    {
        $media = Media::where('id', $mediaId)->firstOrFail();
        $this->contentId = $media->model_id;

        $isVideo = (bool) $media->getCustomProperty('is_video', false);
        if (! $isVideo) {
            throw new RuntimeException('The media provided is not a video');
        }

        $this->duration = (int) $media->getCustomProperty('duration');
        if ($this->duration < Config::integer('content.minimum_duration')) {
            throw new RuntimeException("The video duration is too short: $this->duration");
        }

        // create temp folder
        $this->tempPath = md5("$caller:$media->file_name");
        $this->processingPath = Storage::disk($this->processingDisk)->path($this->tempPath);
        if (! is_dir($this->processingPath) && ! mkdir($this->processingPath, 0777, true) && ! is_dir($this->processingPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->processingPath));
        }

        // prepare local file name
        $videoFileName = pathinfo((string) $media->file_name, PATHINFO_BASENAME);
        $this->localFilePath = sprintf('%s%s%s', $this->processingPath, DIRECTORY_SEPARATOR, $videoFileName);
        $this->relativeVideoPath = sprintf('%s%s%s', $this->tempPath, DIRECTORY_SEPARATOR, $videoFileName);

        // download the video to the processing folder
        $fileContent = Storage::disk('s3')->get($media->getPathRelativeToRoot());
        file_put_contents($this->localFilePath, $fileContent);

        // Load the video into FFMpeg and check it.
        $video = FFMpeg::fromDisk($this->processingDisk)->open($this->relativeVideoPath);
        if (! $video->isVideo()) {
            throw new RuntimeException('The media is not a valid video');
        }

        // Remove the video reference from memory.
        unset($video);
    }

    public function deleteTempFiles(): void
    {
        File::delete($this->localFilePath);
        File::deleteDirectory($this->processingPath);
    }
}
