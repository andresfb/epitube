<?php

namespace App\Console\Commands\Tube;

use App\Services\Tube\SyncFeedRecordsService;
use Exception;
use Illuminate\Console\Command;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\info;

final class RecreateFeedCommand extends Command
{
    protected $signature = 'recreate:feed';

    protected $description = 'Clear all records and recreate the Feed';

    public function handle(SyncFeedRecordsService $service): void
    {
        try {
            clear();
            intro('Recreating the Feed Records');

            if (! confirm('This will delete all Feed records. Continue?', false)) {
                info('bye');

                return;
            }

            $service->setToScreen(true)->execute();
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
