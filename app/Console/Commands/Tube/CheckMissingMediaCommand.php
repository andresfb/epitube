<?php

namespace App\Console\Commands\Tube;

use App\Jobs\Tube\CreatePreviewsJob;
use App\Jobs\Tube\ExtractThumbnailsJob;
use App\Jobs\Tube\GenerateDownscalesJob;
use App\Jobs\Tube\TranscodeVideoJob;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Media;
use App\Models\Tube\MimeType;
use App\Services\Tube\GenerateDownscalesService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\warning;

class CheckMissingMediaCommand extends Command
{
    private const int CHUNK_SIZE = 200;

    protected $signature = 'missing:media';

    protected $description = 'Command description';

    public function __construct(private readonly GenerateDownscalesService $downscalesService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Checking for missing Media...');

            $this->line('Loading Contents');
            $count = 0;
            $total = Content::query()
                ->where('active', true)
                ->where('created_at', '<', now()->startOfDay())
                ->latest()
                ->count();

            info("Found $total Contents");

            Content::query()
                ->where('active', true)
                ->where('created_at', '<', now()->startOfDay())
                ->latest()
                ->chunk(self::CHUNK_SIZE, function (Collection $contents) use (&$count, $total): void {
                    warning(sprintf("\nWorking on the next %s records\n", self::CHUNK_SIZE));

                    $contents->each(function (Content $content): void {
                        try {

                            $this->info("Checking content: $content->id | $content->title. Created on {$content->created_at->toDateTimeString()}");

                            if (!$content->hasMedia(MediaNamesLibrary::videos())) {
                                $this->processMissingMedia($content, MediaNamesLibrary::videos());
                            } else {
                                $this->line('Has Video');
                            }

                            $video = $content->getMedia(MediaNamesLibrary::videos())
                                ->firstOrFail();

                            if (MimeType::needsTranscode($video->mime_type)) {
                                if (! $content->hasMedia(MediaNamesLibrary::transcoded())) {
                                    $this->processMissingMedia($content, MediaNamesLibrary::transcoded());

                                    return;
                                }

                                $this->line('Has Transcoded');
                            }

                            if (! $content->hasMedia(MediaNamesLibrary::thumbnails())) {
                                $this->processMissingMedia($content, MediaNamesLibrary::thumbnails());
                            } else {
                                $this->line('Has Thumbnails');
                            }

                            if (! $content->hasMedia(MediaNamesLibrary::previews())) {
                                $this->processMissingMedia($content, MediaNamesLibrary::previews());
                            } else if ($content->getMedia(MediaNamesLibrary::previews())->count() < 2) {
                                $this->processMissingMedia($content, MediaNamesLibrary::previews());
                            } else {
                                $this->line('Has Previews');
                            }

                            if (! $content->hasMedia(MediaNamesLibrary::downscaled())) {
                                $this->processMissingMedia($content, MediaNamesLibrary::downscaled());
                            } else  {
                                $this->line('Has Downscales');
                            }
                        } finally {
                            $this->info("Next...\n");
                        }
                    });

                    $count += self::CHUNK_SIZE;
                    warning(sprintf("Completed %s of %s Feed records\n", $count, $total));
                });
        } catch (Exception $e) {
            $this->newLine();
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }

    private function processMissingMedia(Content $content, string $collectionName): void
    {
        $this->warn("Missing $collectionName Collection");

        if (($collectionName === MediaNamesLibrary::videos()) && confirm('Content Missing `Videos` collection. Disable Content?')) {
            $content->active = false;
            $content->save();

            return;
        }

        if ($collectionName === MediaNamesLibrary::downscaled()) {
            $media = $this->getMedia($content);
            if (! $this->downscalesService->canConvert($media)) {
                return;
            }

            if (! $this->downscalesService->needsDownscale($media)) {
                return;
            }
        }

        if (! confirm("Queue creating the $collectionName files?")) {
            return;
        }

        $media = $this->getMedia($content);

        match ($collectionName) {
            MediaNamesLibrary::transcoded() => TranscodeVideoJob::dispatch($media->id),
            MediaNamesLibrary::previews() => CreatePreviewsJob::dispatch($media->id),
            MediaNamesLibrary::thumbnails() => ExtractThumbnailsJob::dispatch($media->id),
            MediaNamesLibrary::downscaled() => GenerateDownscalesJob::dispatch($media->id),
        };

        $this->line("Dispatched job to create $collectionName files");
    }

    private function getMedia(Content $content): Media|SpatieMedia
    {
        $collection = MediaNamesLibrary::videos();
        if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
            $collection = MediaNamesLibrary::transcoded();
        }

        return $content->getMedia($collection)
            ->firstOrFail();
    }
}
