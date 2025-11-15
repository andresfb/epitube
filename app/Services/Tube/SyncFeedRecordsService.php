<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use App\Traits\Screenable;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

final class SyncFeedRecordsService
{
    use Screenable;

    private const int CHUNK_SIZE = 200;

    public function execute(): void
    {
        $this->warning("Start to re-create the Feed data\n");

        $this->info('Deleting records');
        Feed::query()->chunk(self::CHUNK_SIZE, function (Collection $feeds) {
            $this->notice(sprintf('Working on the next batch of %s Feed records', self::CHUNK_SIZE));

            $feeds->each(function (Feed $feed) {
                Feed::withoutEvents(function () use ($feed) {
                    $this->character('🚫 ');
                    $feed->forceDelete();
                });
            });

            $this->notice('');
        });
        $this->info("Done deleting records\n");

        $this->info('Clearing the search index');
        Artisan::call('scout:flush', [
            'model' => Feed::class,
        ]);
        $this->info("Done clearing the search index\n");

        $this->info('Creating Feed records');
        $found = false;
        Content::query()
            ->with('related')
            ->hasAllMedia()
            ->chunk(self::CHUNK_SIZE, function (Collection $list) use (&$found): void {
                $this->notice(sprintf('Working on the next batch of %s Feed records', self::CHUNK_SIZE));

                try {
                    $list->each(function (Content $content) use (&$found): void {
                        $found = true;

                        Feed::withoutEvents(function () use ($content) {
                            $this->character('✅ ');
                            Feed::generate($content);
                        });
                    });
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                    $this->notice('');
                }

                $this->notice('');
            });
        $this->info('Done creating Feed records');

        if (! $found) {
            $this->warning('No Content found. 👋');

            return;
        }

        $this->info('Updating Feed missing fields');
        Feed::withoutEvents(static function () {
            Feed::query()
                ->update([
                    'order' => 0,
                    'published' => false,
                ]);
        });
        $this->info('Done updating Feed missing fields');

        $this->info('Recreating search index');
        Artisan::call('scout:import', [
            'model' => Feed::class,
        ]);
        $this->info('Done recreating search index');

        $this->info('Clearing feed cache');
        CacheLibrary::clear(['feed']);

        $this->warning("\nDone recreating Feed data\n");
    }
}
