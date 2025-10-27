<?php

namespace App\Console\Commands\Tube;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class ResetCacheCommand extends Command
{
    protected $signature = 'reset:cache';

    protected $description = 'Flush all tagged cache records';

    public function handle(): void
    {
        try {
            clear();
            intro('Flushing cache records');

            $tags = [
                'categories',
                'feed',
                'list-of-mime-types',
                'transcodable-list-of-mime-types',
                'hls-list-of-mime-types',
                'shared_tags',
                'title-tags',
                'special-tags',
                'tags',
            ];

            Cache::tags($tags)->flush();
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
