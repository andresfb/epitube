<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Jobs\Tube\ExtractThumbnailsJob;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Media;
use App\Services\Tube\ExtractThumbnailsService;
use Exception;
use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class ExtractThumbnailsCommand extends BaseEncodeCommand
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

            $contentId = (int)$this->argument('contentId');
            $content = $this->getContent($contentId);

            if ($content->hasMedia(MediaNamesLibrary::thumbnails())) {
                if (! confirm('Media already has Thumbnails. Continue?')) {
                    return;
                }

                $content->getMedia(MediaNamesLibrary::thumbnails())
                    ->each(function (Media $media) {
                        $media->forceDelete();
                    });
            }

            $media = $this->getMedia($content);
            if (confirm('Dispatch Job?', false)) {
                ExtractThumbnailsJob::dispatch($media->id);
                info('Job Dispatched');

                return;
            }

            info('Executing service...');
            $this->service->execute($media->id);
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
