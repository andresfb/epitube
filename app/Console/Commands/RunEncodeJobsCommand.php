<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\RunExtraJobsAction;
use Exception;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class RunEncodeJobsCommand extends BaseEncodeCommand
{
    protected $signature = 'encode:content {contentId?}';

    protected $description = 'Run all the Encoding jobs for a Content';

    public function __construct(private readonly RunExtraJobsAction $jobsAction)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Running all Jobs');

            $contentId = (int) $this->argument('contentId');
            $content = $this->getContent($contentId);
            $media = $this->getMedia($content);

            $this->jobsAction->handle($media->id);
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
