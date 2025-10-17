<?php

namespace App\Console\Commands\Tube;

use App\Dtos\Tube\ImportVideoItem;
use App\Jobs\Tube\ImportVideoJob;
use App\Libraries\Tube\JellyfinLibrary;
use App\Services\Tube\ImportVideoService;
use App\Traits\ImportItemCreator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

final class ImportVideoPathCommand extends Command
{
    use ImportItemCreator;

    protected $signature = 'import:video-path';

    protected $description = 'Import Video from a given path';

    public function handle(ImportVideoService $service): void
    {
        try {
            clear();
            intro('Starting Import');

            $entry = text('Enter the video path');
            $path = $this->checkEntry($entry);

            $importItem = $this->findItem($path);
            info('Video found');

            if (confirm('Dispatch Job?', app()->isProduction())) {
                ImportVideoJob::dispatch($importItem);
                info('Job Dispatched');

                return;
            }

            info('Executing service...');
            $service->execute($importItem);
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }

    private function checkEntry(string $entry): string
    {
        $file = Str::of($entry)->trim();
        if ($file->isEmpty()) {
            throw new RuntimeException('Enter a valid file path');
        }

        $clientContentPath = Config::string('content.client_data_path');
        if (blank($clientContentPath)) {
            throw new RuntimeException('Client Data Path not set');
        }

        $file = $file->replace($clientContentPath, Config::string('content.data_path'))
            ->replace('\\', '')
            ->replace(['"', "'"], '')
            ->trim()
            ->toString();

        if (! file_exists($file)) {
            throw new RuntimeException("File not found $file");
        }

        return $file;
    }

    private function findItem(string $path): ImportVideoItem
    {
        info("Looking up $path on Jellyfin");

        $items = JellyfinLibrary::getItems();
        if (blank($items)) {
            throw new RuntimeException('No items found on Jellyfin');
        }

        foreach ($items as $item) {
            if ($item['Path'] !== $path) {
                continue;
            }

            return $this->createItem($item);
        }

        throw new RuntimeException("Item $path found on Jellyfin");
    }
}
