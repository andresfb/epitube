<?php

namespace App\Console\Commands;

use App\Jobs\GenerateHlsVideosJob;
use App\Libraries\MediaNamesLibrary;
use App\Services\HlsConverterService;
use Exception;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class GenerateHlsVideosCommand extends BaseEncodeCommand
{
    protected $signature = 'create:hls {contentId?}';

    protected $description = 'Generate HLS videos from content';

    public function __construct(private readonly HlsConverterService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Generate Previews');

            $contentId = (int) $this->argument('contentId');
            $content = $this->getContent($contentId);

            $media = $this->getMedia($content);
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($media->hasGeneratedConversion(MediaNamesLibrary::hlsConversion())
                && ! confirm('Media already has HLS conversion. Continue?')) {

                return;
            }

            if (confirm('Dispatch Job?', false)) {
                GenerateHlsVideosJob::dispatch($media->id);
                info('Job Dispatched');

                return;
            }

            info('Executing service');
            $this->service->execute($media->id);
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
