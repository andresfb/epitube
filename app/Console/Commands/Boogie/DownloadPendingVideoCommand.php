<?php

namespace App\Console\Commands\Boogie;

use App\Dtos\Boogie\DownloadStatusItem;
use App\Libraries\Boogie\DownloadVideoLibrary;
use App\Models\Boogie\SelectedVideo;
use App\Models\Tube\Content;
use Exception;
use Illuminate\Console\Command;
use RuntimeException;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

class DownloadPendingVideoCommand extends Command
{
    protected $signature = 'download:pending';

    protected $description = 'Download one of the Pending Videos in the Boogie database';

    public function __construct(private readonly DownloadVideoLibrary $downloadLibrary)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        clear();
        intro('Starting downloading pending video');

        try {
            $run = 0;
            $makRuns = 100000;
            $pending = null;
            $continue = true;

            while ($continue) {
                if ($run >= $makRuns) {
                    throw new RuntimeException('Too many runs trying to find a pending video');
                }

                $pending = SelectedVideo::query()
                    ->pending()
                    ->firstOrFail();

                $continue = Content::query()
                    ->where('item_id', $pending->hash)
                    ->exists();

                $run++;
            }

            $this->line("Downloading pending video $pending->id | $pending->title");
            $status = new DownloadStatusItem(
                count: 0,
                runs: 0,
                started: now(),
            );

            $this->downloadLibrary->download($pending, $status);
            if (! $this->downloadLibrary->downloaded()) {
                throw new RuntimeException('Video not downloaded');
            }

            dump($this->downloadLibrary->getDownloadPath());
        } catch (Exception $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
