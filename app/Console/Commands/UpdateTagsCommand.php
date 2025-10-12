<?php

namespace App\Console\Commands;

use App\Models\Tube\Content;
use App\Services\ImportVideoService;
use Illuminate\Console\Command;
use Throwable;
use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class UpdateTagsCommand extends Command
{
    protected $signature = 'update:tags';

    protected $description = 'Update the tags for existing Content records';

    public function __construct(private readonly ImportVideoService $videoService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Check for Tags');

            Content::all()->each(function (Content $content) {
                if (blank($content->og_path)) {
                    error("Missing og path on Content: $content->id");

                    return;
                }

                $fileInfo = pathinfo($content->og_path);
                $this->videoService->parseTags($content, $fileInfo);
                echo '.';
            });
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
