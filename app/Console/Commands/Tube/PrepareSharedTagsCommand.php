<?php

namespace App\Console\Commands\Tube;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;

class PrepareSharedTagsCommand extends Command
{
    protected $signature = 'prepare:shared';

    protected $description = 'Prepare the Shared Tags in the require format';

    public function handle(): void
    {
        try {
            clear();
            intro('Prepare Shared Tags');

            $tagList = Config::array('content.shared_tags');

            while (true) {
                $tag = text(
                    label: 'Enter Tag name',
                    required: true,
                    transform: fn($tag): string => ucwords($tag)
                );

                info("Processing $tag");

                $childTags = text(
                    label: 'Enter Child Tags',
                    placeholder: 'Coma separated',
                    required: true,
                );

                $tagList[$tag] = str($childTags)
                    ->explode(',')
                    ->map(fn(string $tag) => ucwords(trim($tag)))
                    ->toArray();

                if (! confirm('Add more Tags?')) {
                    break;
                }

                info('Next tag');
            }

            info('Creating format string...');

            $this->newLine();
            echo base64_encode(json_encode($tagList, JSON_THROW_ON_ERROR));
            $this->newLine();
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
