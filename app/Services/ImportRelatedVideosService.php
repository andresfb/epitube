<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ImportRelatedVideoJob;
use App\Models\Content;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class ImportRelatedVideosService
{
    public function execute(): void
    {
        $contents = Content::query()
            ->whereDoesntHave('related')
            ->where('active', true)
            ->limit(
                Config::integer('content.max_import_videos') * 2
            )
            ->get();

        if ($contents->isEmpty()) {
            Log::info('No Content without related videos found');

            return;
        }

        $contents->each(function (Content $item): void {
            ImportRelatedVideoJob::dispatch($item->id);
        });
    }
}
