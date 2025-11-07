<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Actions\Backend\RunExtraJobsAction;
use App\Exceptions\ProcessRunningException;
use App\Libraries\Tube\MasterVideoLibrary;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Media;
use App\Traits\Encodable;
use Exception;
use FFMpeg\FFProbe;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class TranscodeVideoService
{
    use Encodable;

    private const string TRANSCODE_DISK = 'transcode';

    private int $duration = 0;

    private string $tempPath = '';

    private string $fullPath = '';

    private Media $media;

    private ?Stream $video = null;

    public function __construct(
        private readonly RunExtraJobsAction $action,
        private readonly MasterVideoLibrary $videoLibrary
    ) {}

    /**
     * @throws Exception
     */
    public function execute(int $mediaId): void
    {
        Log::info("Starting transcoding for video: $mediaId");

        $this->media = Media::findOrFail($mediaId);

        try {
            $this->fullPath = $this->media->getPath();
            $this->tempPath = md5($this->fullPath);
            $this->flag = "$this->tempPath/creating";

            Log::info("Checking for $this->flag file");
            $this->checkFlag(
                disk: self::TRANSCODE_DISK,
                mediaId: $this->media->model_id,
                mediaName: $this->media->name,
            );
        } catch (ProcessRunningException $exception) {
            Log::error($exception->getMessage());

            return;
        }

        try {
            Log::info("Creating $this->flag file");
            $this->createFlag(self::TRANSCODE_DISK);

            Log::info('Executing Transcoding process');
            $info = $this->transcode();

            Log::info('Adding transcoded video to Media');
            $newMedia = $this->addNewMedia($info);

            $this->videoLibrary->setMediaId($newMedia->id)
                ->prepareDownloadPath();

            $masterPath = $this->videoLibrary->getMasterFile();

            Log::info("Moving the video to download folder: $masterPath");
            File::move($info['out_file'], $masterPath);

            Log::info('Processing the rest of the Encoding jobs');
            $this->action->handle($newMedia->id);
        } finally {
            $this->deleteFlag(self::TRANSCODE_DISK);

            Storage::disk(self::TRANSCODE_DISK)->deleteDirectory($this->tempPath);
        }
    }

    private function checkEncodedFile(string $file): void
    {
        $fileType = 'Transcoding';

        if (! file_exists($file)) {
            throw new RuntimeException("$fileType file not created");
        }

        if (in_array(filesize($file), [0, false], true)) {
            throw new RuntimeException("$fileType file is empty");
        }

        try {
            chmod($file, 0777);
        } catch (Exception $e) {
            Log::error('@TranscodeVideoService.checkEncodedFile: '.$e->getMessage());
        }

        // has a duration greater than 0
        $probe = FFProbe::create([
            'ffprobe.binaries' => $this->ffProbe(),
        ]);

        if (! $probe->isValid($file)) {
            throw new RuntimeException("$fileType file is not valid");
        }

        $streams = $probe->streams($file);
        $this->video = $streams->videos()->first();
        if ($this->video === null) {
            throw new RuntimeException('No valid video found');
        }

        // comparing master and encoded durations with a 2% threshold
        $this->duration = (int) $probe->format($file)->get('duration');
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

    private function transcode(): array
    {
        Log::info("Transcoding video: {$this->media->model_id} | {$this->media->name}");

        $outputFile = sprintf(
            '%s%s/%s.mp4',
            Storage::disk(self::TRANSCODE_DISK)->path(''),
            $this->tempPath,
            pathinfo($this->fullPath, PATHINFO_FILENAME)
        );

        $cmd = sprintf(
            '"%s" -hide_banner -y -v error -i "%s" -q:v 0 -ar 44100 -ab 128k "%s"',
            $this->ffMpeg(),
            $this->fullPath,
            $outputFile
        );

        Log::channel(Config::string('laravel-ffmpeg.log_channel'))
            ->info("Transcoding ffmpeg running command: $cmd");

        $process = Process::fromShellCommandline($cmd)
            ->setTimeout(0)
            ->mustRun();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        Log::info('Transcoding video finished');

        $this->checkEncodedFile($outputFile);
        [$width, $height] = $this->getVideoSize();

        return [
            'width' => $width,
            'height' => $height,
            'out_file' => $outputFile,
        ];
    }

    private function getVideoSize(): array
    {
        $width = (int) $this->video->get('width', 1280);
        $height = (int) $this->video->get('height', 720);

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
    private function addNewMedia(array $info): SpatieMedia|Media
    {
        $content = Content::where('id', $this->media->model_id)
            ->firstOrFail();

        return $content->addMedia($info['out_file'])
            ->preservingOriginal()
            ->withProperties(['name' => $this->media->name])
            ->withCustomProperties([
                'width' => (int) $info['width'],
                'height' => (int) $info['height'],
                'duration' => $this->duration,
                'owner_id' => $this->media->id,
                'is_video' => true,
                'transcoded' => true,
            ])
            ->toMediaCollection(MediaNamesLibrary::transcoded());
    }
}
