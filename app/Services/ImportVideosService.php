<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\ImportVideoItem;
use App\Jobs\ImportVideoJob;
use App\Models\Content;
use App\Models\MimeType;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Modules\JellyfinApi\Facades\Jellyfin;

final class ImportVideosService
{
    private int $maxFiles = 0;

    private int $scanned = 0;

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        Log::notice('Videos import started at '.now()->toDateTimeString());

        $this->maxFiles = Config::integer('content.max_import_videos');
        $videos = $this->getServiceItems();

        $videos->each(function (ImportVideoItem $videoItem) {
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
        $items = $this->loadFromAPI();
        if ($items === []) {
            Log::error('No videos found to import');

            return $videos;
        }

        $extensions = MimeType::extensions();
        $importedFiles = Content::getImported();

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

            $fileInfo = pathinfo($item['Path']);
            if (! in_array($fileInfo['extension'], $extensions, true)) {
                continue;
            }

            $videos->add(
                new ImportVideoItem(
                    Id: $item['Id'],
                    Name: $fileInfo['filename'],
                    Path: $item['Path'],
                    RunTimeTicks: (int) ($item['RunTimeTicks'] ?? 0),
                    Width: (int) ($item['Width'] ?? 0),
                    Height: (int) ($item['Height'] ?? 0),
                )
            );

            $this->scanned++;
        }

        Log::notice('Loading Items done');

        return $videos;
    }

    private function loadFromAPI(): array
    {
        Log::notice('Calling the Service API');

        return Cache::remember(
            'VIDEOS:FROM:API',
            now()->addDay()->subSeconds(2),
            static function (): array {
                try {
                    Jellyfin::setProvider();
                    $provider = Jellyfin::getProvider();
                    $result = $provider->getItems();

                    if (blank($result)) {
                        Log::error('Api returned empty array');

                        return [];
                    }

                    if (blank($result['Items'])) {
                        Log::error("No items found");

                        return [];
                    }

                    Log::notice("Found {$result['TotalRecordCount']} items");

                    return $result['Items'];
                } catch (Exception $e) {
                    Log::error($e->getMessage());

                    return [];
                }
            }
        );
    }
}
