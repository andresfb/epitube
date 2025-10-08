<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\MediaNamesLibrary;
use Exception;
use FFMpeg\FFProbe;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

final class EncodeDownscaleService extends BaseEncodeService
{
    /**
     * @throws Exception
     */
    public function execute(int $resolution, int $mediaId): void
    {
        try {
            Log::notice("Starting downscaling video for media: $mediaId to resolution: $resolution");

            $this->prepare($mediaId, (string) $resolution);

            $ffProbe = FFProbe::create([
                'ffprobe.binaries' => $this->ffProbe(),
            ]);

            $file = $this->downscale($resolution);
            if (! $ffProbe->isValid($file)) {
                throw new RuntimeException("File: $file is not a valid video");
            }

            $streams = $ffProbe->streams($file)->videos()->first();
            if ($streams === null) {
                throw new RuntimeException("No video streams found for $file");
            }

            Log::notice('Adding downscale to Media');
            $this->videoLibrary->getContent()->addMedia($file)
                ->withCustomProperties([
                    'width' => (int) $streams->getDimensions()->getWidth(),
                    'height' => (int) $streams->getDimensions()->getHeight(),
                    'duration' => (int) $ffProbe->format($file)->get('duration'),
                    'owner_id' => $mediaId,
                    'is_video' => true,
                ])
                ->toMediaCollection(MediaNamesLibrary::downscaled());

            $this->videoLibrary->getContent()->searchableSync();
            Log::notice('Done Downscaling video');
        } finally {
            $this->videoLibrary->deleteTempFiles();

            $this->deleteFlag($this->videoLibrary->getProcessingDisk());
        }
    }

    private function downscale(int $resolution): string
    {
        $outputFile = sprintf(
            '%s/%s_%s.mp4',
            $this->videoLibrary->getProcessingPath(),
            pathinfo($this->videoLibrary->getMasterFile(), PATHINFO_FILENAME),
            $resolution,
        );

        $cmd = sprintf(
            '"%s" -hide_banner -y -v error -i "%s" -vf "scale=-2:%s" -crf 15 -c:a copy "%s"',
            $this->ffMpeg(),
            $this->videoLibrary->getMasterFile(),
            $resolution,
            $outputFile,
        );

        Log::info("Downscaling ffmpeg running command: $cmd");
        Process::fromShellCommandline($cmd)
            ->setTimeout(0)
            ->mustRun();

        Log::info('Downscaling video finished');

        return $outputFile;
    }
}
