<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;

trait Encodable
{
    private string $flag = '';

    private function checkFlag(string $disk, int $mediaId, string $mediaName): void
    {
        if (! Storage::disk($disk)->exists($this->flag)) {
            return;
        }

        throw new RuntimeException(
            sprintf("%s | %s %s process already running.", $mediaId, $mediaName, self::class)
        );
    }

    private function createFlag(string $disk): void
    {
        Storage::disk($disk)->put($this->flag, '1');
    }

    private function deleteFlag(string $disk): void
    {
        Storage::disk($disk)->delete($this->flag);
    }

    private function ffMpeg(): string
    {
        return (new ExecutableFinder)->find('ffmpeg', config('media-library.ffmpeg_path', 'ffmpeg'));
    }

    private function ffProbe(): string
    {
        return (new ExecutableFinder)->find('ffprobe', config('media-library.ffprobe_path', 'ffprobe'));
    }
}
