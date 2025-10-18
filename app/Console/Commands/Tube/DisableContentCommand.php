<?php

namespace App\Console\Commands\Tube;

use App\Models\Tube\Content;
use Illuminate\Console\Command;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class DisableContentCommand extends Command
{
    protected $signature = 'content:disable {content?}';

    protected $description = 'Disable a given Content';

    public function handle(): void
    {
        try {
            clear();
            intro('Disabling Content');

            if ($this->hasArgument('content')) {
                $contentId = (int) $this->argument('content');
            } else {
                $contentId = (int) text(
                    label: 'Enter Content ID',
                    required: true,
                    validate: 'numeric'
                );
            }

            $content = Content::query()
                ->where('id', $contentId)
                ->firstOrFail();

            warning("Disabling Content: $content->title");
            if (! confirm('Continue?', false)) {
                info('bye');

                return;
            }

            $content->active = false;
            $content->save();
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
