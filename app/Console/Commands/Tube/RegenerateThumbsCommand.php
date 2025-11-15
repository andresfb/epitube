<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Jobs\Tube\RegenerateThumbsJob;
use App\Libraries\Tube\CacheLibrary;
use App\Models\Tube\Content;
use Exception;
use Illuminate\Console\Command;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\warning;

final class RegenerateThumbsCommand extends Command
{
    protected $signature = 'regenerate:thumbs';

    protected $description = 'Regenerate the Thumbnails of all Content Records';

    public function handle(): void
    {
        try {
            clear();
            intro('Regenerating Thumbnails');

            if (! confirm('This will delete all existing Thumbnails. Continue?', false)) {
                info('bye');

                return;
            }

            $this->line('Loading Contents');
            $contents = Content::query()
                ->oldest()
                ->get();

            if ($contents->isEmpty()) {
                warning('No Contents Found');

                return;
            }

            $this->newLine();
            $this->line("Processing {$contents->count()} contents");
            $this->newLine();

            $contents->each(function (Content $content) {
                RegenerateThumbsJob::dispatch($content->id);
                echo '.';
            });

            $this->newLine();
            CacheLibrary::clear(['feed']);
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
