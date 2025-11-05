<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Jobs\Tube\ImportRelatedVideoJob;
use App\Models\Tube\Content;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class ImportRelatedVideosService
{
    private array $checkedList = [];

    public function __construct(private readonly ImportRelatedVideoService $relatedVideoService){}

    /**
     * This method used to dispatch a job for each related video
     * Used by the scheduler via ImportRelatedVideosJob
     */
    public function execute(): void
    {
        Log::notice('Start importing related videos');

        $contents = $this->getContents();
        if ($contents === false) {
            return;
        }

        $delay = 0;
        $contents->each(function (Content $item) use (&$delay): void {
            if (in_array($item->id, $this->checkedList, true)) {
                return;
            }

            $delay += 5;
            ImportRelatedVideoJob::dispatch($item->id, $delay);
        });

        Log::notice('End importing related videos');
    }

    /**
     * This method will run the Related Video import service directly.
     * Mostly used in the Artisan command `php artisan import:related`
     */
    public function handle(): void
    {
        Log::notice('Start importing related videos');

        $contents = $this->getContents();
        if ($contents === false) {
            return;
        }

        $contents->each(function (Content $item): void {
            if (in_array($item->id, $this->checkedList, true)) {
                return;
            }

            $this->relatedVideoService->execute($item->id);
        });

        Log::notice('End importing related videos');
    }

    private function getContents(): Collection|bool
    {
        $this->checkedList = $this->getCheckedList();

        $contents = Content::query()
            ->usable()
            ->whereDoesntHave('related')
            ->whereNotIn('id', $this->checkedList)
            ->limit(
                Config::integer('content.max_import_videos') * 2
            )
            ->get();

        if ($contents->isEmpty()) {
            Log::warning('No Content without related videos found');

            return false;
        }

        Log::notice("Found {$contents->count()} Content records missing related videos");

        return $contents;
    }

    private function getCheckedList(): array
    {
        $key = md5(Config::string('content.related_checks_key'));
        return array_map(intval(...), Cache::get($key, []));
    }
}
