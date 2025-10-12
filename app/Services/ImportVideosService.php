<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\ImportVideoItem;
use App\Jobs\ImportVideoJob;
use App\Libraries\JellyfinLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Rejected;
use App\Traits\ImportItemCreator;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class ImportVideosService
{
    use ImportItemCreator;

    private int $maxFiles;

    private int $scanned = 0;

    public function __construct()
    {
        $this->maxFiles = Config::integer('content.max_import_videos');
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        Log::notice('Videos import started at '.now()->toDateTimeString());

        $this->getServiceItems()->each(function (ImportVideoItem $videoItem): void {
            Log::notice("Queueing file: $videoItem->Path for importing");
            ImportVideoJob::dispatch($videoItem);
        });

        Log::notice('Videos import ended at '.now()->toDateTimeString());
    }

    /**
     * @return Collection<ImportVideoItem>
     */
    private function getServiceItems(): Collection
    {
        Log::notice('Loading the Service Items');

        $videos = collect();
        $items = JellyfinLibrary::getItems();
        if (blank($items)) {
            Log::error('No videos found to import');

            return $videos;
        }

        $importedFiles = Content::getImported();
        $rejected = Rejected::getRejected();
        shuffle($items);

        foreach ($items as $item) {
            if ($this->scanned >= $this->maxFiles) {
                break;
            }

            if (! file_exists($item['Path'])) {
                continue;
            }

            if (in_array($item['Id'], $importedFiles, true)) {
                continue;
            }

            if (in_array($item['Id'], $rejected, true)) {
                continue;
            }

            $videos->add($this->createItem($item));
            $this->scanned++;
        }

        Log::notice('Loading Items done');

        return $videos;
    }
}
