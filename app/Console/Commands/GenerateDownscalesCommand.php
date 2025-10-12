<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateDownscalesJob;
use App\Libraries\MediaNamesLibrary;
use App\Models\Tube\Media;
use App\Services\GenerateDownscalesService;
use Exception;
use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class GenerateDownscalesCommand extends BaseEncodeCommand
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

            if ($content->hasMedia(MediaNamesLibrary::downscaled())) {
                if (! confirm('Media already has Downscaled videos. Continue?')) {
                    return;
                }

                $content->getMedia(MediaNamesLibrary::downscaled())->each(function (Media $media) {
                    $media->forceDelete();
                });
            }

            $media = $this->getMedia($content);
            if (confirm('Dispatch Job?', false)) {
                GenerateDownscalesJob::dispatch($media->id);
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
