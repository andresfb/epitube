<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Test app command';

    public function handle(): void
    {
        try {
            clear();
            intro('Starting test');

        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
