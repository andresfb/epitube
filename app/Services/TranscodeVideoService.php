<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Media;
use Exception;
use FFMpeg\FFProbe;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class TranscodeVideoService
{
    private const TRANSCODE_DISK = 'transcode';

    private int $duration = 0;
    private string $flag = '';
    private string $tempPath = '';
    private string $fullPath = '';

    private Media $media;
    private ?Stream $video = null;

    /**
     * @throws Exception
     */
    public function execute(int $mediaId): int
    {
        Log::info('Starting '.__CLASS__.' execute');

        $this->media = Media::findOrFail($mediaId);

        try {
            $this->fullPath = $this->media->getPath();
            $this->tempPath = md5($this->fullPath);
            $this->flag = "$this->tempPath/creating";

            Log::info("Checking for {$this->flag} file");
            if (Storage::disk(self::TRANSCODE_DISK)->exists($this->flag)) {
                throw new RuntimeException("{$this->media->model_id} | {$this->media->name} Transcode process already running.");
            }

            Log::info("Creating $this->flag file");
            $this->createFlag();

            Log::info("Executing Transcoding process");
            $info = $this->transcode();

            return $this->addNewMedia($info);
        } finally {
            $this->deleteFlag();
        }
    }

    private function transcode(): array
    {
        Log::info("Transcoding video: {$this->media->model_id} | {$this->media->name}");
        $outputFile = sprintf(
            '%s%s/%s.mp4',
            Storage::disk(self::TRANSCODE_DISK)->path(''),
            $this->tempPath,
            pathinfo($this->fullPath, PATHINFO_FILENAME)
        );

        $baseCmd = config('media-library.ffmpeg_path').' -y -v error -i "%s" -q:v 0 -ar 44100 -ab 128k "%s"';
        $cmd = sprintf($baseCmd, $this->fullPath, $outputFile);

        Log::info("Transcoding ffmpeg running command: $cmd");

        Process::fromShellCommandline($cmd)
            ->setTimeout(0)
            ->mustRun();

        Log::info('Transcoding video finished');

        $this->checkEncodedFile($outputFile);
        [$width, $height] = $this->getVideoSize();

        return [
            'width' => $width,
            'height'   => $height,
            'out_file' => $outputFile,
        ];
    }

    protected function checkEncodedFile(string $file): void
    {
        $fileType = 'Transcoding';

        if (! file_exists($file)) {
            throw new RuntimeException("$fileType file not created");
        }

        if (! filesize($file)) {
            throw new RuntimeException("$fileType file is empty");
        }

        try {
            chmod($file, 0777);
        } catch (Exception $e) {
            Log::error('@TranscodeVideoService.checkEncodedFile: '.$e->getMessage());
        }

        // has a duration greater than 0
        $probe = FFProbe::create();
        if (! $probe->isValid($file)) {
            throw new RuntimeException("$fileType file is not valid");
        }

        $streams = $probe->streams($file);
        $this->video = $streams->videos()->first();
        if ($this->video === null) {
            throw new RuntimeException("No valid video found");
        }

        // comparing master and encoded durations with a 2% threshold
        $this->duration = (int) round($probe->format($file)->get('duration'));
        $originalDuration = (int) $this->media->getCustomProperty('duration');
        $threshold = 0.05 * $originalDuration;
        $difference = abs($originalDuration - $this->duration);
        if ($difference > $threshold) {
            throw new RuntimeException("$fileType file is not complete");
        }

        // has a video stream
        foreach ($streams->videos() as $video) {
            if (! $video->isVideo()) {
                continue;
            }

            return;
        }

        throw new RuntimeException("$fileType file is not a video");
    }

    private function getVideoSize(): array
    {
        $width = (int) $this->video->get('width', 1280);
        $height =  (int) $this->video->get('height', 720);

        if ($height > $width) {
            $height += $width;
            $width = $height - $width;
            $height -= $width;
        }

        return [$width, $height];
    }

    /**
     * @throws Exception
     */
    private function addNewMedia(array $info): int
    {
        $content = Content::where('id', $this->media->model_id)
            ->firstOrFail();

        $media = $content->addMedia($info['out_file'])
            ->withProperties(['name' => $this->media->name])
            ->withCustomProperties([
                'width' => $info['width'],
                'height' => $info['height'],
                'duration' => $this->duration,
                'is_video' => true,
                'owner_id' => $this->media->id,
            ])
            ->toMediaCollection('transcoded');

        return $media->id;
    }

    private function createFlag(): void
    {
        Storage::disk(self::TRANSCODE_DISK)->put($this->flag, '1');
    }

    private function deleteFlag(): void
    {
        Storage::disk(self::TRANSCODE_DISK)->delete($this->flag);

        Storage::disk(self::TRANSCODE_DISK)->deleteDirectory($this->tempPath);
    }
}
