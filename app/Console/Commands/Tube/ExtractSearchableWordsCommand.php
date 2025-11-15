<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Models\Tube\Content;
use App\Services\Tube\SearchableWordsService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class ExtractSearchableWordsCommand extends Command
{
    protected $signature = 'extract:words';

    protected $description = 'Extract searchable words from Contents';

    public function __construct(private readonly SearchableWordsService $service)
    {
        parent::__construct();
        $this->service->setToScreen(true);
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Extract Words');

            $chunkSize = 200;
            $count = 0;
            $total = Content::query()
                ->without(['media'])
                ->where('active', true)
                ->count();

            Content::query()
                ->without(['media'])
                ->where('active', true)
                ->oldest()
                ->chunk($chunkSize, function (Collection $contents) use (&$count, $total, $chunkSize): void {
                    $this->info(sprintf('Working on the next %s records', $chunkSize));

                    $contents->each(function (Content $content) {
                        $this->service->execute($content);
                    });

                    $count += $chunkSize;
                    $this->info(sprintf("\nCompleted %s of %s Content records", $count, $total));
                    $this->newLine();
                });
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
