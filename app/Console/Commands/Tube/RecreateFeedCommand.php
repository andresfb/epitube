<?php

namespace App\Console\Commands\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class RecreateFeedCommand extends Command
{
    protected $signature = 'recreate:feed';

    protected $description = 'Command description';

    public function handle(): void
    {
        try {
            clear();
            intro('Recreating the Feed Records');

            if (! confirm('This will delete all Feed records. Continue?', false)) {
                info('bye');

                return;
            }

            $this->line('Deleting records...');
            Feed::all()->each(fn (Feed $feed) => $feed->forceDelete());

            $this->line('Loading Contents');
            $contents = Content::query()
                ->hasVideos()
                ->hasThumbnails()
                ->get();

            if ($contents->isEmpty()) {
                warning('No Contents Found');

                return;
            }

            $this->newLine();
            $this->line("Processing {$contents->count()} contents");
            $this->newLine();

            $this->line('Creating feed...');
            $contents->each(function (Content $content) {
                Feed::generate($content);
                echo '.';
            });

            $this->newLine();
            Cache::tags('feed')->flush();
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
