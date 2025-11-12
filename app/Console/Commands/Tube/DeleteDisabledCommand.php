<?php

namespace App\Console\Commands\Tube;

use App\Services\Tube\DeleteDisabledService;
use Exception;
use Illuminate\Console\Command;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class DeleteDisabledCommand extends Command
{
    protected $signature = 'delete:disabled';

    protected $description = 'Delete all disabled Contents and their related records';

    public function handle(DeleteDisabledService $service): void
    {
        try {
            clear();
            intro('Deleting disabled Content');

            $service->setToScreen(true)
                ->execute();
        } catch (Exception $e) {
            $this->newLine();
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
