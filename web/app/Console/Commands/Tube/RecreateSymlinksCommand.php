<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Actions\Backend\CreateSymLinksAction;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use Exception;
use Illuminate\Console\Command;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class RecreateSymlinksCommand extends Command
{
    protected $signature = 'recreate:symlinks';

    protected $description = 'Re-create any media missing symlinks in the Content folder';

    public function __construct(private readonly CreateSymLinksAction $linksAction)
    {
        parent::__construct();
        $this->linksAction->setToScreen(true);
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Recreating Symlinks');

            Content::query()
                ->hasVideos()
                ->whereActive(true)
                ->oldest()
                ->each(function (Content $content) {
                    $this->createLinks($content);
                });

            $this->newLine();
        } catch (Exception $e) {
            $this->newLine();
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }

    private function createLinks(Content $content): void
    {
        $video = $content->getMedia(MediaNamesLibrary::videos())->first();
        if ($video === null) {
            echo 'x'.PHP_EOL;

            return;
        }

        $this->linksAction->handle(
            media: $video,
            skipDelete: true
        );

        $this->newLine();
    }
}
