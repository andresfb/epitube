<?php

namespace App\Services;

use App\Models\Content;
use App\Models\MimeType;
use Exception;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFProbe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\TemporaryDirectory;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class HlsConverterService
 *
 * Based on https://github.com/Astrotomic/laravel-medialibrary-hls/tree/main
 */
class HlsConverterService
{
    public const string RES_360P  =  '360p';
    public const string RES_480P  =  '480p';
    public const string RES_720P  =  '720p';
    public const string RES_1080P = '1080p';
    public const string RES_1440P = '1440p';
    public const string RES_2160P = '2160p';

    // https://medium.com/@peer5/creating-a-production-ready-multi-bitrate-hls-vod-stream-dff1e2f1612c
    public const array RESOLUTIONS = [
        // name => [width, height, video-bitrate, audio-bitrate]
        self::RES_360P =>  [-2,  360,   900,  96],
        self::RES_480P =>  [-2,  480,  1600, 128],
        self::RES_720P =>  [-2,  720,  3200, 192],
        self::RES_1080P => [-2, 1080,  5300, 192],
        self::RES_1440P => [-2, 1440, 11000, 192],
        self::RES_2160P => [-2, 2160, 18200, 192],
    ];

    public function __construct(protected readonly Filesystem $filesystem) {}

    /**
     * @throws Exception
     */
    public function execute(int $mediaId): void
    {
        Log::notice("Starting creating HLS playlist for: $mediaId");
        $media = Media::where('id', $mediaId)
            ->firstOrFail();

        if (! $this->canConvert($media)) {
            throw new RuntimeException("Media not supported: {$media->id}");
        }

        $temporaryDirectory = TemporaryDirectory::create()->deleteWhenDestroyed();
        $copiedOriginalFile = $this->filesystem->copyFromMediaLibrary(
            $media,
            $temporaryDirectory->path(Str::random(32) . '.' . $media->extension)
        );

        $filepath = $this->convert($copiedOriginalFile);
        $directory = dirname($filepath);

        foreach (File::allFiles($directory) as $file) {
            $this->filesystem->copyToMediaLibrary(
                $file->getPathname(),
                $media,
                'conversions',
                "hls/{$file->getRelativePathname()}"
            );
        }

        $media->markAsConversionGenerated('hls');

        $diskRelativePath = "/{$this->filesystem->getConversionDirectory($media)}.'hls/playlist.m3u8'";

        // todo: save the list of generated resolutions

        $media->setCustomProperty('hls', $diskRelativePath);
        $content = Content::where('id', $media->model_id)->first();
        $content?->touch();

        unlink($copiedOriginalFile);
        Log::notice('Done creating HLS playlist');
    }

    public function convert(string $file): string
    {
        $output = dirname($file).'/hls';
        if (! mkdir($output, 0777, true) && ! is_dir($output)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $output));
        }

        $ffProbe = FFProbe::create([
            'ffprobe.binaries' => $this->ffProbe(),
        ]);

        if (! $ffProbe->isValid($file)) {
            throw new RuntimeException("File: $file is not a valid video");
        }

        $resolutions = $this->getResolutions(
            $ffProbe->streams($file)->videos()->first()?->getDimensions()
        );

        // https://gist.github.com/Andrey2G/78d42b5c87850f8fbadd0b670b0e6924
        $command = implode(' ', [
            $this->ffMpeg(),
            "-n -i \"{$file}\"",
            $resolutions->map(fn(): string => '-map 0:v:0 -map 0:a:0')->implode(' '),
            '-c:v h264 -crf 20 -c:a aac -ar 48000',
            $resolutions
                ->values()
                ->map(fn(array $r, int $i): string => "-filter:v:{$i} scale=w={$r[0]}:h={$r[1]}:force_original_aspect_ratio=decrease -maxrate:v:{$i} {$r[2]}k -b:a:{$i} {$r[3]}k")
                ->implode(' '),
            Str::of(
                $resolutions
                    ->keys()
                    ->map(fn(string $name, int $i): string => "v:{$i},a:{$i},name:{$name}")
                    ->implode(' ')
            )->prepend('-var_stream_map "')->append('"'),
            '-preset slow -hls_list_size 0 -threads 0 -f hls -hls_playlist_type event -hls_time 4 -hls_flags independent_segments -master_pl_name "playlist.m3u8"',
            "-hls_segment_filename \"{$output}/%v/%04d.ts\"",
            "\"{$output}/%v/playlist.m3u8\"",
        ]);

        Log::info("HLS conversion command: $command");

        Process::fromShellCommandline($command)
            ->setTimeout(0)
            ->mustRun();

        Log::info('HLS conversion finished');

        // todo: list the generated resolutions.

        return $output.'/playlist.m3u8';
    }

    protected function canConvert(Media $media): bool
    {
        if (! $this->requirementsAreInstalled()) {
            return false;
        }

        return $this->canHandleMimeType(Str::lower($media->mime_type));
    }

    protected function canHandleMimeType(string $mime): bool
    {
        return collect(MimeType::canHls())->contains($mime);
    }

    protected function requirementsAreInstalled(): bool
    {
        return class_exists(FFProbe::class)
            && file_exists($this->ffProbe())
            && file_exists($this->ffMpeg());
    }

    protected function ffMpeg(): string
    {
        return (new ExecutableFinder)->find('ffmpeg', config('media-library.ffmpeg_path', 'ffmpeg'));
    }

    protected function ffProbe(): string
    {
        return (new ExecutableFinder)->find('ffprobe', config('media-library.ffprobe_path', 'ffprobe'));
    }

    protected function getResolutions(Dimension $dimensions): Collection
    {
        return collect(self::RESOLUTIONS)
            ->filter(fn(array $resolution): bool => $resolution[1] <= $dimensions->getHeight());
    }
}
