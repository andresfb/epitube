<?php

namespace App\Console\Commands\Boogie;

use App\Jobs\Boogie\CheckSelectedVideosJob;
use Illuminate\Console\Command;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\info;

class CheckSelectedVideosCommand extends Command
{
    protected $signature = 'selected:videos';

    protected $description = 'Start checking the selected videos from Boogie database';

    public function handle(): void
    {
        clear();
        intro('Starting downloading');

        try {
            CheckSelectedVideosJob::dispatch();

            info('Job dispatched');
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
