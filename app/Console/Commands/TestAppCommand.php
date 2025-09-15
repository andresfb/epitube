<?php

namespace App\Console\Commands;

use App\Libraries\TitleParserLibrary;
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

    public function handle(TitleParserLibrary $library): void
    {
        try {
            clear();
            intro('Starting test');

            $files = collect($this->getRawData());
            $fileInfo = pathinfo($files->random());
            $title = $library->parseFileName($fileInfo);

            $this->line("\n");
            dump($fileInfo, str($title)->title()->toString());

        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }

    private function getRawData(): array
    {
        return [];
    }
}
