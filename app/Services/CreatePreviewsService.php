<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\MasterVideoLibrary;
use App\Libraries\MediaNamesLibrary;
use App\Models\Content;
use Exception;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

final readonly class CreatePreviewsService
{
    public function __construct(private MasterVideoLibrary $videoLibrary) {}

    /**
     * @throws Exception
     */
    public function execute(int $mediaId): void
    {
        Log::notice("Starting creating Preview videos for: $mediaId");
        $this->videoLibrary->prepare($mediaId, self::class);

        $this->generate(
            Content::where('id', $this->videoLibrary->getContentId())
                ->firstOrFail()
        );

        $this->videoLibrary->deleteTempFiles();
        Log::notice('Done creating Preview videos');
    }

    /**
     * @throws Exception
     */
    private function generate(Content $content): void
    {
        try {
            Log::notice('Generating Preview videos');
            $sections = $this->calculateSections($this->videoLibrary->getDuration());

            foreach (Config::array('content.preview_options.sizes') as $size => $bitRate) {
                $size = (int) $size;
                $bitRate = (int) $bitRate;

                if ($this->videoLibrary->getHeight() < $size) {
                    continue;
                }

                foreach (Config::array('content.preview_options.extensions') as $extension) {
                    $file = $this->createClipFile($size, $bitRate, $extension, $sections);
                    $fullPath = Storage::disk($this->videoLibrary->getProcessingDisk())
                        ->path($file);

                    $content->addMedia($fullPath)
                        ->withCustomProperties([
                            'size' => $size,
                            'extension' => $extension,
                            'is_video' => true,
                        ])
                        ->toMediaCollection(MediaNamesLibrary::previews());
                }
            }

            $content->searchableSync();
            Log::notice('Done creating Preview videos');
        } catch (Exception $e) {
            File::deleteDirectory($this->videoLibrary->getProcessingPath());
            $content->getMedia(MediaNamesLibrary::previews())
                ->each(function ($media) {
                    $media->forceDelete();
                });

            throw $e;
        }
    }

    private function createClipFile(int $size, int $bitRate, string $extension, array $sections): string
    {
        $fileTemplate = sprintf(
            '%s/preview_%s%s.%s',
            $this->videoLibrary->getTempPath(),
            $size,
            '%s',
            $extension
        );

        $tmpFileTemplate = sprintf($fileTemplate, '_%s');
        $outputFile = sprintf($fileTemplate, '');

        Log::notice("Encoding $outputFile file");

        $tmpFiles = [];
        foreach ($sections as $section) {
            $video = FFMpeg::fromDisk($this->videoLibrary->getDownloadDisk())
                ->open($this->videoLibrary->getRelativeVideoPath());

            $tmpFile = sprintf(
                $tmpFileTemplate,
                mb_str_pad($section['index'], 2, '0', STR_PAD_LEFT)
            );

            $video->export()
                ->addFilter(function (VideoFilters $filters) use ($section): void {
                    $filters->clip(
                        TimeCode::fromSeconds($section['start']),
                        TimeCode::fromSeconds($section['duration']),
                    );
                })
                ->addFilter('-crf', 15)
                ->addFilter('-an')
                ->addFilter(function (VideoFilters $filters) use ($size): void {
                    $filters->custom("fps=10,scale=-2:$size:flags=lanczos");
                })
                ->toDisk($this->videoLibrary->getProcessingDisk())
                ->inFormat($this->getEncodeFormat($bitRate))
                ->save($tmpFile);

            $tmpFiles[] = $tmpFile;

            unset($video);
        }

        FFMpeg::fromDisk($this->videoLibrary->getProcessingDisk())
            ->open($tmpFiles)
            ->export()
            ->concatWithoutTranscoding()
            ->save($outputFile);

        Storage::disk($this->videoLibrary->getProcessingDisk())
            ->delete($tmpFiles);

        return $outputFile;
    }

    private function calculateSections(int $duration): array
    {
        $trimmedDuration = $duration
            - ($duration * (Config::integer('content.preview_options.padding_time') / 100));
        $sectionDuration = Config::integer('content.preview_options.section_length');
        $sectionsPerInterval = Config::integer('content.preview_options.sections');
        $intervalDuration = 20 * 60; // 20 minutes in seconds

        // Calculate the total number of sections needed
        $totalSections = max(
            $sectionsPerInterval, // minimum 3 sections
            ceil($trimmedDuration / $intervalDuration) * $sectionsPerInterval
        );

        // Calculate spacing between sections
        $availableDuration = $trimmedDuration - ($totalSections * $sectionDuration);
        $spacing = $availableDuration / ($totalSections + 1);

        $sections = [];
        for ($i = 0; $i < $totalSections; $i++) {
            $index = $i + 1;
            $startTime = $index * $spacing + ($i * $sectionDuration);

            // Ensure the section fits within the video duration
            if ($startTime + $sectionDuration <= $trimmedDuration) {
                $sections[] = [
                    'index' => (string) $index,
                    'start' => $startTime,
                    'duration' => $sectionDuration
                ];
            }
        }

        return $sections;
    }

    private function getEncodeFormat(string $extension, int $bitRate): WebM|X264
    {
        $format = $extension !== 'mp4'
            ? new X264('libmp3lame')
            : new WebM('libvorbis');

        return $format->setKiloBitrate($bitRate);
    }
}
