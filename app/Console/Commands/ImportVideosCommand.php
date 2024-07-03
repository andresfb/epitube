<?php

namespace App\Console\Commands;

use App\Services\ImportVideosService;
use Exception;
use Illuminate\Console\Command;

class ImportVideosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans the Content path and import the files into the Content Model';

    public function handle(ImportVideosService $service): int
    {
        try {
            $this->info("Starting Import");
            $this->newLine();

            $service->execute();

            $this->newLine();
            $this->info("Done");

            return 0;
        } catch (Exception $e) {
            $this->error("\nError found:\n");
            $this->error($e->getMessage());
            $this->info("");

            return 1;
        }
    }
}
