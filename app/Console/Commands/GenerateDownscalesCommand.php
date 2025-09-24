<?php

namespace App\Console\Commands;

use App\Jobs\GenerateDownscalesJob;
use App\Libraries\MediaNamesLibrary;
use App\Services\GenerateDownscalesService;
use Exception;

use RuntimeException;
use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class GenerateDownscalesCommand extends BaseEncodeCommand
{
    protected $signature = 'create:downscales {contentId?}';

    protected $description = 'Generate Downscaled videos from content';

    public function __construct(private readonly GenerateDownscalesService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Generate Downscales');

            $contentId = (int) $this->argument('contentId');
            $content = $this->getContent($contentId);

            $collection = MediaNamesLibrary::videos();
            if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
                $collection = MediaNamesLibrary::transcoded();
            }

            $medias = $content->getMedia($collection);
            if ($medias->count() > 1
                && ! confirm('Media already has Downscaled videos. Continue?')) {
                return;
            }

            $mediaId = $medias->first()?->id;
            if (blank($mediaId)) {
                throw new RuntimeException('Media not found');
            }

            if (confirm('Dispatch Job?', false)) {
                GenerateDownscalesJob::dispatch($mediaId);
                info('Job Dispatched');

                return;
            }

            info('Executing service...');
            $this->service->execute($mediaId);
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
