<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Jobs\Tube\EncodeDownscaleJob;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Media;
use App\Services\Tube\GenerateDownscalesService;
use Illuminate\Console\Command;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class CompleteDownscalesCommand extends Command
{
    protected $signature = 'complete:downscales';

    protected $description = 'Look for and complete any missing downscales';

    public function handle(GenerateDownscalesService $service): void
    {
        try {
            clear();
            intro('Looking for missing downscales');

            Content::query()
                ->get()
                ->each(function (Content $content) use ($service) {
                    $downscaled = $content->getMedia(MediaNamesLibrary::downscaled());
                    if ($downscaled->isEmpty()) {
                        echo ' ❌ ';

                        return;
                    }

                    $collection = MediaNamesLibrary::videos();
                    if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
                        $collection = MediaNamesLibrary::transcoded();
                    }

                    $media = $content->getMedia($collection)->first();
                    if ($media === null) {
                        echo ' 🚫 ';

                        return;
                    }

                    $mediaHeight = (int) $media->getCustomProperty('height');
                    $resolutions = $service->getResolutions($mediaHeight);
                    if ($resolutions->count() === $downscaled->count()) {
                        echo ' ✅ ';

                        return;
                    }

                    $missing = $resolutions->count() - $downscaled->count();
                    $this->line("\n\nContent: $content->id missing $missing downscale(s)");

                    if (! confirm('Dispatch Job?')) {
                        $this->newLine();

                        return;
                    }

                    foreach ($resolutions as $resolution) {
                        $downscaled->each(function (Media $item) use ($resolution, $media) {
                            $height = (int) $item->getCustomProperty('height');
                            if ($height === $resolution) {
                                return;
                            }

                            EncodeDownscaleJob::dispatch($resolution, $media->id);
                            $this->line('dispatched');
                            $this->newLine();
                        });
                    }

                    $this->newLine();
                });
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine(2);
            outro('Done');
        }
    }
}
