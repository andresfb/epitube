<?php

declare(strict_types=1);

namespace App\Traits;

use App\Exceptions\ProcessRunningException;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;

trait Encodable
{
    protected string $flag = '';

    /**
     * @throws ProcessRunningException
     */
    protected function checkFlag(string $disk, int $mediaId, string $mediaName): void
    {
        if (! Storage::disk($disk)->exists($this->flag)) {
            return;
        }

        throw new ProcessRunningException(
            sprintf('%s | %s %s process already running.', $mediaId, $mediaName, self::class)
        );
    }

    protected function createFlag(string $disk): void
    {
        Storage::disk($disk)->put($this->flag, '1');
    }

    protected function deleteFlag(string $disk): void
    {
        Storage::disk($disk)->delete($this->flag);
    }

    protected function ffMpeg(): string
    {
        return (new ExecutableFinder)->find('ffmpeg', config('media-library.ffmpeg_path', 'ffmpeg'));
    }

    protected function ffProbe(): string
    {
        return (new ExecutableFinder)->find('ffprobe', config('media-library.ffprobe_path', 'ffprobe'));
    }
}
