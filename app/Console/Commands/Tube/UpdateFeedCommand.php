<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Services\Tube\UpdateFeedService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\text;

final class UpdateFeedCommand extends Command
{
    protected $signature = 'update:feed';

    protected $description = 'Update the Feed with the latest Content changes';

    public function handle(UpdateFeedService $service): void
    {
        try {
            clear();
            intro('Updating feed');

            $value = text(
                label: 'From what Date?',
                placeholder: 'YYYY-MM-DD',
                default: now()->subDay()->toDateString(),
                required: true,
            );

            $fromDate = CarbonImmutable::parse($value)->endOfDay();

            $count = text(
                label: 'How Many',
                default: (string) Config::integer('content.max_import_videos'),
                required: true,
                validate: 'integer'
            );

            $this->newLine();
            $service->setToScreen(true)
                ->execute($fromDate, (int) $count);
        } catch (Throwable $e) {
            $this->newLine();
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
