<?php

namespace App\Console\Commands\Tube;

use App\Dtos\Tube\ImportVideoItem;
use App\Jobs\Tube\ImportVideoJob;
use App\Libraries\Tube\JellyfinLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Media;
use App\Models\Tube\Rejected;
use App\Services\Tube\ImportVideoService;
use App\Traits\ImportItemCreator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

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

            $this->checkRejected($importItem);
            $this->checkImported($importItem);

            if (confirm('Dispatch Job?', app()->isProduction())) {
                ImportVideoJob::dispatch($importItem);
                info('Job Dispatched');

                return;
            }

            info('Executing service...');
            $service->execute($importItem);
        } catch (InvalidOptionException) {
            $this->newLine();
            warning('User cancelled');
        } catch (Throwable $e) {
            $this->newLine();
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

    private function checkImported(ImportVideoItem $importItem): void
    {
        $content = Content::query()
            ->where('item_id', $importItem->Id)
            ->where('og_path', $importItem->Path)
            ->first();

        if ($content === null) {
            return;
        }

        if (! confirm('Content already imported, overwrite?')) {
            throw new InvalidOptionException('');
        }

        $content->getMedia()->each(fn (Media $media) => $media->forceDelete());
    }

    private function checkRejected(ImportVideoItem $importItem): void
    {
        $rejected = Rejected::query()
            ->where('item_id', $importItem->Id)
            ->first();

        if ($rejected === null) {
            return;
        }

        throw new RuntimeException("Item $importItem->Path rejected because \"$rejected->reason\"");
    }
}
