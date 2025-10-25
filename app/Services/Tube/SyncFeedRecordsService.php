<?php

namespace App\Services\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncFeedRecordsService
{
    public function execute(): void
    {
        Log::info('Start to re-create the Feed data');

        Log::notice('Deleting records');
        Feed::query()->chunk(200, function (Collection $feeds) {
            $feeds->each(function (Feed $feed) {
                Feed::withoutEvents(static function () use ($feed) {
                    $feed->forceDelete();
                });
            });
        });

        Log::notice('Clearing the search index');
        Artisan::call('scout:flush', [
            'model' => Feed::class,
        ]);

        Log::notice('Processing Contents');

        $found = false;
        Content::query()
            ->with('related')
            ->hasVideos()
            ->hasThumbnails()
            ->chunk(200, function (Collection $list) use (&$found): void {
                try {
                    $list->each(function (Content $content) use (&$found): void {
                        $found = true;

                        Feed::withoutEvents(static function () use ($content) {
                            Feed::generate($content);
                        });
                    });
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                }
            });

        if (! $found) {
            Log::warning('No Content found');

            return;
        }

        Log::notice('Updating Feed missing fields');
        Feed::withoutEvents(static function () {
            Feed::query()
                ->update([
                    'order' => 0,
                    'published' => false,
                ]);
        });

        Log::notice('Recreating search index');
        Artisan::call('scout:import', [
            'model' => Feed::class,
        ]);

        Log::notice('Clearing feed cache');
        Cache::tags('feed')->flush();

        Log::info('Done recreating Feed data');
    }
}
