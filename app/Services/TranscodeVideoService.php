<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Media;
use Exception;
use FFMpeg\FFProbe;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
    public function execute(int $mediaId): void
    {
        Log::info('Starting '.__CLASS__.' execute');

        $this->media = Media::where('id', $mediaId)
            ->firstOrFail();

        try {
            $this->fullPath = $this->media->getPath();
            $this->tempPath = md5($this->fullPath);
            $this->flag = "$this->tempPath/creating";

            Log::info("Checking for {$this->flag} file");
            if (Storage::disk(self::TRANSCODE_DISK)->exists($this->flag)) {
                throw new \RuntimeException("{$this->media->model_id} | {$this->media->name} Video is being Transcoded");
            }

            Log::info("Creating $this->flag file");

            $this->createFlag();

            Log::info("Executing Transcoding process");

            $info = $this->transcode();
            $this->addNewMedia($info);
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

        // TODO: test the transcode command manually on a wmv and avi videos
        $baseCmd = config('media-library.ffmpeg_path').' -y -v error -i "%s" -q:v 0 -ar 44100 -ab 128k "%s"';
        $cmd = sprintf($baseCmd, $this->fullPath, $outputFile);

        Log::info("Transcoding ffmpeg running command: $cmd");

        shell_exec($cmd);

        $this->checkEncodedFile($outputFile);
        $height = $this->getVideoHeight();

        return [
            'height'   => $height,
            'out_file' => $outputFile,
        ];
    }

    protected function checkEncodedFile(string $file): void
    {
        $fileType = 'Transcoding';

        if (!file_exists($file)) {
            throw new \RuntimeException("$fileType file not created");
        }

        if (!filesize($file)) {
            throw new \RuntimeException("$fileType file is empty");
        }

        try {
            chmod($file, 0777);
        } catch (Exception $e) {
            Log::error('@TranscodeVideoService.checkEncodedFile: '.$e->getMessage());
        }

        // has a duration greater than 0
        $probe = FFProbe::create();
        if (!$probe->isValid($file)) {
            throw new \RuntimeException("$fileType file is not valid");
        }

        $streams = $probe->streams($file);
        $this->video = $streams->videos()->first();
        if ($this->video === null) {
            throw new \RuntimeException("No valid video found");
        }

        // comparing master and encoded durations with a 2% threshold
        $this->duration = (int) round($probe->format($file)->get('duration'));
        $originalDuration = (int) $this->media->getCustomProperty('height');
        $threshold = 0.05 * $originalDuration;
        $difference = abs($originalDuration - $this->duration);
        if ($difference > $threshold) {
            throw new \RuntimeException("$fileType file is not complete");
        }

        // has a video stream
        foreach ($streams->videos() as $video) {
            if (!$video->isVideo()) {
                continue;
            }

            return;
        }

        throw new \RuntimeException("$fileType file is not a video");
    }

    private function getVideoHeight(): int
    {
        $width = (int) $this->video->get('width', 1280);
        $height =  (int) $this->video->get('height', 720);

        if ($height > $width) {
            $height += $width;
            $width = $height - $width;
            $height -= $width;
        }

        return $height;
    }

    /**
     * @throws Exception
     */
    private function addNewMedia(array $info): void
    {
        $content = Content::where('id', $this->media->model_id)
            ->firstOrFail();

        $content->addMedia($info['out_file'])
            ->withCustomProperties([
                'height' => $info['height'],
                'duration' => $this->duration,
            ])
            ->toMediaCollection('transcoded');
    }

    private function createFlag(): void
    {
        Storage::disk(self::TRANSCODE_DISK)->put($this->flag, '1');
    }

    private function deleteFlag(): void
    {
        Storage::disk(self::TRANSCODE_DISK)->delete($this->flag);
    }
}
