<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Libraries\Tube\MediaNamesLibrary;
use App\Services\Tube\CreatePreviewsService;
use Exception;
use Illuminate\Support\Facades\Config;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\warning;

final class CreateMissingPreviewsCommand extends BaseEncodeCommand
{
    protected $signature = 'missing:previews {contentId?}';

    protected $description = 'Look for and create missing previews';

    public function __construct(private readonly CreatePreviewsService $service)
    {
        parent::__construct();
        $this->service->setToScreen(true);
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Generate Missing Previews');

            $contentId = (int) $this->argument('contentId');
            $content = $this->getContent($contentId);
            $previews = $content->getMedia(MediaNamesLibrary::previews());
            if ($previews->isEmpty()) {
                warning('No previews found.');
            }

            $items = array_merge(
                Config::array('content.preview_options.sizes'),
                Config::array('content.preview_options.extensions')
            );

            /** @noinspection NotOptimalIfConditionsInspection */
            if ($previews->count() === count($items) && ! confirm('All previews found. Continue?')) {
                return;
            }

            info('Executing service...');
            $this->service->generateMissing($content->id);
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
