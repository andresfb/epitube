<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Test app command';

    public function handle(): void
    {
        Log::notice('Test started at: '.now()->format('Y-m-d H:i:s'));

        try {
            clear();
            intro('Starting test');

        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            Log::notice('Test finished at: '.now()->format('Y-m-d H:i:s'));
            $this->newLine();
            outro('Done');
        }
    }
}
