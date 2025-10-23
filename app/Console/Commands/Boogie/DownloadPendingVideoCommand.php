<?php

namespace App\Console\Commands\Boogie;

use App\Dtos\Boogie\DownloadStatusItem;
use App\Dtos\Boogie\ImportSelectedVideoItem;
use App\Libraries\Boogie\DownloadVideoLibrary;
use App\Models\Boogie\SelectedVideo;
use App\Services\Boogie\ImportSelectedVideoService;
use App\Traits\LanguageChecker;
use Illuminate\Console\Command;
use LanguageDetector\LanguageDetector;
use RuntimeException;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class DownloadPendingVideoCommand extends Command
{
    use LanguageChecker;

    protected $signature = 'download:pending';

    protected $description = 'Download one of the Pending Videos in the Boogie database';

    public function __construct(
        private readonly DownloadVideoLibrary $downloadLibrary,
        private readonly LanguageDetector $detector,
        private readonly ImportSelectedVideoService $importService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        clear();
        intro('Starting downloading pending video');

        try {
            $this->line('Finding pending video');
            $pending = SelectedVideo::query()
                ->pending()
                ->firstOrFail();

            $this->newLine();
            $this->line('Checking video');

            if (! filter_var($pending->url, FILTER_VALIDATE_URL)) {
                error("Invalid URL: $pending->url on video: $pending->id");
                $pending->disable();

                return;
            }

            if ($this->containsNonLatin($pending->title)) {
                error("Title is not on Latin characters for video: $pending->id");
                $pending->disable();

                return;
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

            $this->line('Video downloaded. Importing...');
            $this->importService->execute(
                new ImportSelectedVideoItem(
                    videoId: $pending->id,
                    downloadPath: $this->downloadLibrary->getDownloadPath(),
                )
            );

            info('Import successful');
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
