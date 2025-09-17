<?php

namespace App\Services;

use App\Libraries\MasterVideoLibrary;
use App\Libraries\MediaNamesLibrary;
use App\Models\Content;
use Exception;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

readonly class ExtractThumbnailsService
{
    public function __construct(private MasterVideoLibrary $videoLibrary) {}

    /**
     * @throws Exception
     */
    public function execute(int $mediaId): void
    {
        Log::notice("Starting extracting thumbnails for: $mediaId");
        $this->videoLibrary->prepare($mediaId, __CLASS__);

        $this->generate(
            Content::where('id', $this->videoLibrary->getContentId())
                ->firstOrFail()
        );

        $this->videoLibrary->deleteTempFiles();
        Log::notice('Done extracting thumbnails');
    }

    /**
     * @throws Exception
     */
    private function generate(Content $content): void
    {
        $images = $this->extract();

        foreach ($images as $image) {
            $content->addMedia($image)
                ->withCustomProperties([
                    'is_video' => false,
                ])
                ->toMediaCollection(MediaNamesLibrary::thumbnails());
        }

        $content->touch();
    }

    /**
     * @throws Exception
     */
    private function extract(): array
    {
        $images = [];
        $numberThumbnails = Config::integer('content.thumbnails.total');
        $duration = $this->videoLibrary->getDuration();
        $skip = random_int(3, 7) / 100;
        $timeCode = floor(($duration - ($duration * $skip)) / $numberThumbnails);

        $video = FFMpeg::fromDisk($this->videoLibrary->getProcessingDisk())
            ->open($this->videoLibrary->getRelativeVideoPath());

        for ($i = 1; $i <= $numberThumbnails; $i++) {
            $image = sprintf(
                '%s/%s.png',
                $this->videoLibrary->getTempPath(),
                str_pad((string) $i, 2, '0', STR_PAD_LEFT)
            );

            $time = TimeCode::fromSeconds($timeCode * $i);
            $cmd = sprintf(
                '%s -hide_banner -y -v error -ss "%s" -i "%s" -vframes 1 -f image2 -vf "scale=\'trunc(ih*dar):ih\',setsar=1/1" "%s"',
                Config::string('laravel-ffmpeg.ffmpeg.binaries'),
                $time,
                $this->videoLibrary->getLocalFilePath(),
                Storage::disk($this->videoLibrary->getProcessingDisk())->path($image),
            );

            $process = Process::fromShellCommandline($cmd)
                ->setTimeout(0)
                ->mustRun();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $images[] = Storage::disk($this->videoLibrary->getProcessingDisk())->path($image);
            $video = $video->fresh();
        }

        return $images;
    }
}
