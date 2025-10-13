<?php

namespace App\Libraries\Boogie;

use App\Dtos\Boogie\DownloadStatusItem;
use App\Models\Boogie\SelectedVideo;
use App\Traits\DirectoryChecker;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

final class DownloadVideoLibrary
{
    use DirectoryChecker;

    private bool $downloaded = false;

    private string $downloadPath = '';

    public function downloaded(): bool
    {
        return $this->downloaded;
    }

    public function getDownloadPath(): string
    {
        return $this->downloadPath;
    }

    public function download(SelectedVideo $video, DownloadStatusItem $status): DownloadStatusItem
    {
        Log::notice("Starting download process for video: $video->url on video $video->id");

        $this->downloaded = false;
        $this->downloadPath = '';

        if (! filter_var($video->url, FILTER_VALIDATE_URL)) {
            Log::error("Invalid URL: $video->url on video $video->id");
            $video->disable();

            return $status;
        }

        $this->downloadPath = sprintf(
            "%s/%s",
            Config::string('selected-videos.download_path'),
            $video->hash,
        );

        if (! is_dir($this->downloadPath) && ! mkdir($this->downloadPath, 0777, true) && ! is_dir($this->downloadPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->downloadPath));
        }

        $cmd = str(Config::string('selected-videos.download_command'))
            ->replace('{0}', $this->downloadPath)
            ->replace('{1}', $video->url)
            ->toString();

        Log::channel(Config::string('laravel-ffmpeg.log_channel'))
            ->info("Downloading video with command: $cmd");

        Log::notice('Downloading video...');
        try {
            $process = Process::fromShellCommandline($cmd)
                ->enableOutput()
                ->setTimeout(0)
                ->mustRun();

            if (!$process->isSuccessful()) {
                throw new RuntimeException($process->getErrorOutput());
            }
        } catch (Exception $e) {
            Log::error("Error downloading video $video->id: {$e->getMessage()}");
            $video->disable();
            $this->deleteTempFolder();

            return $status->incrementRuns();
        }

        Log::info("Video $video->id downloaded successfully");
        $video->markedUsed();
        $this->downloaded = true;

        return $status->increment();
    }

    private function deleteTempFolder(): void
    {
        if (blank($this->downloadPath)) {
            return;
        }

        if (! $this->isDirectoryEmpty($this->downloadPath)) {
            return;
        }

        File::deleteDirectory($this->downloadPath);
    }
}
