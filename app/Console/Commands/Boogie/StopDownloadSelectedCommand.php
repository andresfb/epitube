<?php

declare(strict_types=1);

namespace App\Console\Commands\Boogie;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class StopDownloadSelectedCommand extends Command
{
    protected $signature = 'stop:downloads';

    protected $description = 'Stop the Selected Videos download process';

    public function handle(): void
    {
        clear();
        intro('Stoping the Download Process');

        try {
            $statusKey = md5(Config::string('selected-videos.download_status_key'));
            Cache::forget($statusKey);

            $processKey = md5(Config::string('selected-videos.process_key'));
            Redis::del($processKey);
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
