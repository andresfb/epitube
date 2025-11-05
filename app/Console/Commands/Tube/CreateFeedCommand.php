<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Services\Tube\CreateFeedService;
use Illuminate\Console\Command;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class CreateFeedCommand extends Command
{
    protected $signature = 'create:feed';

    protected $description = 'Publish some Feed records';

    public function handle(CreateFeedService $service): void
    {
        try {
            clear();
            intro('Publishing the Feed');

            $service->execute();
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
