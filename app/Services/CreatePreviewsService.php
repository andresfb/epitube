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
        Log::notice('Generating Preview videos');

        foreach (Config::array('content.preview_options.sizes') as $size => $bitRate) {
            foreach (Config::array('content.preview_options.extensions') as $extension) {
                $file = $this->createClipFile($size, $bitRate, $extension);

                $content->addMedia($file)
                    ->withCustomProperties([
                        'size' => $size,
                        'extension' => $extension,
                        'is_video' => true,
                    ])
                    ->toMediaCollection(MediaNamesLibrary::previews());
            }
        }

        $content->searchableSync();
    }

    private function createClipFile(int $size, int $bitRate, string $extension): string
    {
        $fileTemplate = sprintf(
            '%s/preview_%s_%s%s.%s',
            $this->videoLibrary->getTempPath(),
            $this->videoLibrary->getContentId(),
            $size,
            '%s',
            $extension
        );

        $tmpFileTemplate = sprintf($fileTemplate, '_%s');
        $outputFile = sprintf($fileTemplate, '');

        Log::notice("Encoding $outputFile file");

        $duration = $this->videoLibrary->getDuration();
        $trimmedDuration = $duration - ($duration * (Config::integer('content.preview_options.padding_time') / 100));
        $startTime = $duration - $trimmedDuration;
        $sections = Config::integer('content.preview_options.sections');
        $sectionTime = ceil($trimmedDuration / $sections);
        $tmpFiles = [];

        for ($i = 0; $i < $sections; $i++) {
            $video = FFMpeg::fromDisk($this->videoLibrary->getDownloadDisk())
                ->open($this->videoLibrary->getRelativeVideoPath());

            $tmpFile = sprintf(
                $tmpFileTemplate,
                mb_str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)
            );

            $video->export()
                ->addFilter(function (VideoFilters $filters) use ($startTime): void {
                    $filters->clip(
                        TimeCode::fromSeconds($startTime),
                        TimeCode::fromSeconds(Config::integer('content.preview_options.section_length'))
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
            $startTime += $sectionTime;

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

    private function getEncodeFormat(int $bitRate): WebM|X264
    {
        $format = new X264();
        $format->setKiloBitrate($bitRate)
            ->setAudioCodec("libmp3lame");

        return $format;
    }
}
