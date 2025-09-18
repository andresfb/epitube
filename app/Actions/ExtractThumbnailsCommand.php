<?php

namespace App\Actions;

use App\Console\Commands\BaseEncodeCommand;
use App\Jobs\ExtractThumbnailsJob;
use App\Libraries\MediaNamesLibrary;
use App\Services\ExtractThumbnailsService;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\info;

class ExtractThumbnailsCommand extends BaseEncodeCommand
{
    protected $signature = 'extract:thumbs {contentId?}';

    protected $description = 'Extract thumbnails from content';

    public function __construct(private readonly ExtractThumbnailsService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Extract Thumbnails');

            $contentId = (int) $this->argument('contentId');
            $content = $this->getContent($contentId);

            /** @noinspection NotOptimalIfConditionsInspection */
            if ($content->hasMedia(MediaNamesLibrary::thumbnails())
                && ! confirm('Media already has Thumbnails. Continue?')) {
                return;
            }

            $media = $this->getMedia($content);
            if (confirm('Dispatch Job?', false)) {
                ExtractThumbnailsJob::dispatch($media->id);
                info('Job Dispatched');

                return;
            }

            info('Executing service');
            $this->service->execute($media->id);
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
