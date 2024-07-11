<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Test app command';

    public function handle(): int
    {
        try {
            $this->info("Starting test");
            $this->newLine();

            $this->newLine();
            $this->info("Done");

            return 0;
        } catch (Exception $e) {
            $this->newLine();
            $this->warn("Error found");
            $this->error($e->getMessage());
            $this->newLine();

            return 1;
        }
    }
}
