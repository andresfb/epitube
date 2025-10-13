<?php

namespace App\Services\Boogie;

use App\Dtos\Boogie\DownloadStatusItem;
use App\Jobs\Boogie\CheckSelectedVideosJob;
use App\Jobs\Boogie\DownloadSelectedVideoJob;
use App\Libraries\Boogie\DownloadVideoLibrary;
use App\Models\Boogie\SelectedVideo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

final readonly class DownloadSelectedVideoService
{
    private string $statusKey;

    public function __construct(private DownloadVideoLibrary $downloadLibrary)
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

        $status = $this->downloadLibrary->download($video, $status);
        $this->dispatchJob($status);

        if (! $this->downloadLibrary->downloaded()) {
            return;
        }

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
