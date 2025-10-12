<?php

namespace App\Services;

use App\Dtos\DownloadStatusItem;
use App\Jobs\CheckSelectedVideosJob;
use App\Jobs\DownloadSelectedVideoJob;
use App\Models\Boogie\SelectedVideo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Symfony\Component\Process\Process;

final readonly class DownloadSelectedVideoService
{
    private string $statusKey;

    public function __construct()
    {
        $this->statusKey = md5(Config::string('selected-videos.download_status_key'));
    }

    public function execute(): void
    {
        $processKey = md5(Config::string('selected-videos.process_key'));

        $status = Cache::get($this->statusKey);
        if (! $status instanceof DownloadStatusItem) {
            Log::error('Download status not found');
            Redis::unlink($processKey);

            return;
        }

        $limitRun = Config::integer('selected-videos.limit_run');
        if ($status->runs >= $limitRun * 5) {
            Log::warning('Concurrent runs limit reached');
            Redis::unlink($processKey);

            return;
        }

        if (now() > $status->started->endOfDay()) {
            Log::warning('Downloads are done for the day');
            Redis::unlink($processKey);

            return;
        }

        if ($status->count >= $limitRun) {
            Log::warning('Download limit reached');
            Redis::unlink($processKey);

            return;
        }

        $videoId = (int) Redis::lpop($processKey);
        if (blank($videoId)) {
            Log::warning('No more Selected Videos to download');
            CheckSelectedVideosJob::dispatch();

            return;
        }

        $video = SelectedVideo::where('id', $videoId)->first();
        if ($video === null) {
            Log::warning("Video $videoId not found");
            $this->dispatchJob($status);

            return;
        }

        $this->downloadVideo($video, $status);
    }

    private function downloadVideo(SelectedVideo $video, DownloadStatusItem $status): void
    {
        if (! filter_var($video->url, FILTER_VALIDATE_URL)) {
            Log::error("Invalid URL: $video->url on video $video->id");
            $video->disable();
            $this->dispatchJob($status);

            return;
        }

        $downloadPath = sprintf(
            "%s/%s",
            Config::string('selected-videos.download_path'),
            $video->hash,
        );

        if (! is_dir($downloadPath) && ! mkdir($downloadPath, 0777, true) && ! is_dir($downloadPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $downloadPath));
        }

        $cmd = sprintf(
            Config::string('selected-videos.download_command'),
            $downloadPath,
            $video->url,
        );

        $process = Process::fromShellCommandline($cmd)
            ->enableOutput()
            ->setTimeout(0)
            ->mustRun();

        if (! $process->isSuccessful()) {
            Log::error("Error downloading video $video->id: {$process->getErrorOutput()}");
            $video->disable();
            $this->dispatchJob($status->incrementRuns());

            return;
        }

        $video->markedUsed();
        $this->dispatchJob($status->increment());

        // TODO: dispatch a new job to import the video
    }

    private function dispatchJob(DownloadStatusItem $status): void
    {
        Cache::put(
            $this->statusKey,
            $status,
            now()->endOfDay()->subSeconds(5),
        );

        DownloadSelectedVideoJob::dispatch();
    }
}
