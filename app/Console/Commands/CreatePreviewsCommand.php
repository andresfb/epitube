<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\CreatePreviewsJob;
use App\Libraries\MediaNamesLibrary;
use App\Models\Media;
use App\Services\CreatePreviewsService;
use Exception;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class CreatePreviewsCommand extends BaseEncodeCommand
{
    protected $signature = 'generate:previews {contentId?}';

    protected $description = 'Generate Previews from content';

    public function __construct(private readonly CreatePreviewsService $service)
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

            if ($content->hasMedia(MediaNamesLibrary::previews())) {
                if (! confirm('Media already has Previews. Continue?')) {
                    return;
                }

                $content->getMedia(MediaNamesLibrary::previews())->each(function (Media $media) {
                    $media->forceDelete();
                });
            }

            $media = $this->getMedia($content);
            if (confirm('Dispatch Job?', false)) {
                CreatePreviewsJob::dispatch($media->id);
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
