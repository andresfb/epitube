<?php

namespace App\Services;

use App\Dtos\DownloadStatusItem;
use App\Jobs\DownloadSelectedVideoJob;
use App\Models\Boogie\SelectedVideo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CheckSelectedVideosService
{
    public function execute(): void
    {
        $selected = SelectedVideo::query()
            ->pending()
            ->limit(
                Config::integer('selected-videos.limit_run') * 2
            )
            ->get();

        if ($selected->isEmpty()) {
            Log::error('No pending Selected Videos found');

            return;
        }

        $processKey = md5(Config::string('selected-videos.process_key'));
        $selected->each(function (SelectedVideo $selectedVideo) use($processKey): void {
            Redis::rpush($processKey, $selectedVideo->id);
        });

        Redis::expire(
            md5(Config::string('selected-videos.process_key')),
            3300 // 55 minutes
        );

        $this->dispathJob();
    }

    private function dispathJob(): void
    {
        $statusKey = md5(Config::string('selected-videos.download_status_key'));

        $status = Cache::get($statusKey);
        if (! $status instanceof DownloadStatusItem) {
            $status = new DownloadStatusItem(
                count: 0,
                runs: 0,
                started: now(),
            );
        }

        Cache::put(
            $statusKey,
            $status,
            now()->endOfDay()->subSeconds(5),
        );

        DownloadSelectedVideoJob::dispatch();
    }
}
