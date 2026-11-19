<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Jobs\Tube\SearchableWordsFromContentJob;
use App\Jobs\Tube\SyncFeedJob;
use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Tag;
use App\Traits\Screenable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final class MergeTagsService
{
    use Screenable;

    /**
     * @throws Throwable
     */
    public function execute(Tag $source, Tag $destination): int
    {
        if ($source->is($destination)) {
            throw new InvalidArgumentException('Source and destination tags must be different.');
        }

        return DB::transaction(function () use ($source, $destination): int {
            $contentIds = Content::query()
                ->withAnyTags([$source])
                ->pluck('id');

            Content::query()
                ->whereIn('id', $contentIds)
                ->orderBy('id')
                ->each(function (Content $content) use ($source, $destination): void {
                    $content->attachTag($destination);
                    $content->detachTag($source);
                });

            $source->delete();

            $ids = $contentIds->all();

            DB::afterCommit(static function () use ($ids): void {
                CacheLibrary::clear();

                foreach ($ids as $contentId) {
                    SyncFeedJob::dispatch($contentId);
                    SearchableWordsFromContentJob::dispatch($contentId);
                }
            });

            $this->character('.');

            return $contentIds->count();
        });
    }
}
