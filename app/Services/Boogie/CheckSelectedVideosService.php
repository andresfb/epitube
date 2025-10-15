<?php

namespace App\Services\Boogie;

use App\Dtos\Boogie\DownloadStatusItem;
use App\Jobs\Boogie\DownloadSelectedVideoJob;
use App\Models\Boogie\SelectedVideo;
use App\Traits\LanguageChecker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use LanguageDetector\LanguageDetector;

readonly class CheckSelectedVideosService
{
    use LanguageChecker;

    public function __construct(private LanguageDetector $detector) {}

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
            if (! filter_var($selectedVideo->url, FILTER_VALIDATE_URL)) {
                Log::error("Invalid URL: $selectedVideo->url on video $selectedVideo->id");
                $selectedVideo->disable();

                return;
            }

            if ($this->containsNonLatin($selectedVideo->title)) {
                Log::error("Title is not on Latin characters for video: $selectedVideo->id");
                $selectedVideo->disable();

                return;
            }

            Redis::rpush($processKey, $selectedVideo->id);
        });

        Redis::expire(
            md5(Config::string('selected-videos.process_key')),
            3300 // 55 minutes
        );

        $this->dispatchJob();
    }

    private function dispatchJob(): void
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
