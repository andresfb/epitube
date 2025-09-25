<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\ImportVideoItem;
use App\Jobs\ImportVideoJob;
use App\Models\Content;
use App\Models\Rejected;
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

        $videos->each(function (ImportVideoItem $videoItem): void {
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
        if (blank($items)) {
            Log::error('No videos found to import');

            return $videos;
        }

        $importedFiles = Content::getImported();
        $rejected = Rejected::getRejected();

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

            $fileInfo = pathinfo((string) $item['Path']);
            $videos->add(
                new ImportVideoItem(
                    Id: $item['Id'],
                    Name: $fileInfo['filename'],
                    Path: $item['Path'],
                    MimeType: mime_content_type($item['Path']),
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

        $result = Cache::remember(
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
                        Log::error('No items found');

                        return [];
                    }

                    return $result;
                } catch (Exception $e) {
                    Log::error($e->getMessage());

                    return [];
                }
            }
        );

        Log::notice("Found {$result['TotalRecordCount']} items");

        return $result['Items'];
    }
}
