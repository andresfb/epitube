<?php

declare(strict_types=1);

namespace App\Libraries\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Media;
use App\Traits\Screenable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Filesystem;

final class MasterVideoLibrary
{
    use Screenable;

    private int $duration = 0;

    private int $height = 0;

    private int $mediaId = 0;

    private string $processingDisk;

    private string $downloadDisk;

    private string $tempPath = '';

    private string $relativeVideoPath = '';

    private string $processingPath = '';

    private string $masterFile = '';

    private ?Media $media = null;

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
        if (blank($this->mediaId)) {
            throw new RuntimeException('Missing media id');
        }

        if ($this->media !== null) {
            return $this->media;
        }

        $this->media = Media::where('id', $this->mediaId)
            ->firstOrFail();

        return $this->media;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function prepare(int $mediaId, string $caller): void
    {
        $this->notice("Preparing Master Video for: $mediaId from caller $caller");

        $this->mediaId = $mediaId;
        $this->content = Content::where('id', $this->getMedia()->model_id)
            ->firstOrFail();

        if (! $this->getMedia()->getCustomProperty('is_video', false)) {
            throw new RuntimeException('The media provided is not a video');
        }

        $this->downloadMaster($this->getMedia());
        $this->loadVideoInfo($this->getMedia()->id);

        $this->notice('Creating temp path for processing');
        $this->tempPath = md5("$caller:{$this->getMedia()->file_name}");
        $this->processingPath = Storage::disk($this->processingDisk)->path($this->tempPath);
        if (! is_dir($this->processingPath) && ! mkdir($this->processingPath, 0777, true) && ! is_dir($this->processingPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->processingPath));
        }
    }

    public function downloadMaster(Media $media): void
    {
        if (! $media->getCustomProperty('transcoded', false)) {
            $this->notice('The video is not transcoded, using the original file');
            $this->downloadDisk = DiskNamesLibrary::content();
            $this->masterFile = $media->getPath();
            $this->relativeVideoPath = $media->getPathRelativeToRoot();

            return;
        }

        $this->prepareDownloadPath($media);

        if (! File::exists($this->masterFile)) {
            // download the video from S3
            $this->notice("Downloading video file: $this->masterFile");
            $this->filesystem->copyFromMediaLibrary($media, $this->masterFile);
        }
    }

    public function loadVideoInfo(int $mediaId): void
    {
        $this->notice('Loading video info');
        $this->mediaId = $mediaId;
        $this->height = (int) $this->getMedia()->getCustomProperty('height', 0);
        $this->duration = (int) $this->getMedia()->getCustomProperty('duration', 0);
        if ($this->duration < Config::integer('content.minimum_duration')) {
            throw new RuntimeException("The video duration is too short: $this->duration");
        }
    }

    public function prepareDownloadPath(Media $media): void
    {
        $this->notice('Preparing download path for video');
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
        $this->notice("Deleting temp files on $this->processingPath");
        File::deleteDirectory($this->processingPath);
    }
}
