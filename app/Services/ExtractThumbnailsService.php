<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ProcessRunningException;
use App\Libraries\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;
use Exception;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class ExtractThumbnailsService extends BaseEncodeService
{
    /**
     * @throws Exception
     */
    public function execute(int $mediaId): void
    {
        Log::notice("Starting extracting thumbnails for: $mediaId");

        try {
            $this->prepare($mediaId);
        } catch (ProcessRunningException $exception) {
            Log::error($exception->getMessage());

            return;
        }

        try {
            $this->generate($this->videoLibrary->getContent());

            Log::notice("Finished extracting thumbnails for: $mediaId");
        } finally {
            $this->videoLibrary->deleteTempFiles();

            $this->deleteFlag($this->videoLibrary->getProcessingDisk());
        }
    }

    /**
     * @throws Exception
     */
    private function generate(Content $content): void
    {
        $images = $this->extract();

        Log::notice('Saving thumbnails to Media');
        foreach ($images as $image) {
            $content->addMedia($image)
                ->withCustomProperties([
                    'is_video' => false,
                ])
                ->toMediaCollection(MediaNamesLibrary::thumbnails());
        }

        $content->searchableSync();
        Feed::updateIfExists($content);
    }

    /**
     * @throws Exception
     */
    private function extract(): array
    {
        Log::notice('Extracting thumbnails');

        $images = [];
        $numberThumbnails = Config::integer('content.thumbnails.total');
        $duration = $this->videoLibrary->getDuration();
        $skip = random_int(2, 8) / 100;
        $timeCode = floor(($duration - ($duration * $skip)) / $numberThumbnails);

        for ($i = 1; $i <= $numberThumbnails; $i++) {
            $image = sprintf(
                '%s/%s.png',
                $this->videoLibrary->getTempPath(),
                mb_str_pad((string) $i, 2, '0', STR_PAD_LEFT)
            );

            $time = TimeCode::fromSeconds($timeCode * $i);
            $cmd = sprintf(
                '"%s" -hide_banner -y -v error -ss "%s" -i "%s" -vframes 1 -f image2 -vf "scale=\'trunc(ih*dar):ih\',setsar=1/1" "%s"',
                $this->ffMpeg(),
                $time,
                $this->videoLibrary->getMasterFile(),
                Storage::disk($this->videoLibrary->getProcessingDisk())->path($image),
            );

            Log::notice("Generating $image");
            Log::channel(Config::string('laravel-ffmpeg.log_channel'))
                ->info("Generating $image with cmd: $cmd");

            $process = Process::fromShellCommandline($cmd)
                ->setTimeout(0)
                ->mustRun();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $images[] = Storage::disk($this->videoLibrary->getProcessingDisk())->path($image);
        }

        Log::notice('Done extracting thumbnails');

        return $images;
    }
}
