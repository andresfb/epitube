<?php

namespace App\Services;

use App\Models\Content;
use Exception;
use FFMpeg\FFProbe;

class ImportVideoService
{
    /**
     * @throws Exception
     */
    public function execute(array $fileData): void
    {
        $fileHash = hash_file('md5', $fileData['file']);
        if (Content::found($fileHash)) {
            return;
        }

        [$duration, $height] = $this->getVideoInfo($fileData['file']);

        $fileInfo = pathinfo($fileData['file']);
        $fullName = $this->parseFileName($fileInfo);

        $content = Content::create([
            'name_hash' => $fileData['hash'],
            'file_hash' => $fileHash,
            'title' => $fullName,
            'active' => true,
            'og_path' => $fileData['file']
        ]);

        $tags = $this->extractTags($fileInfo);

        $content->attachTags($tags);

        // TODO: remove the ->preservingOriginal() flag
        $content->addMedia($fileData['file'])
            ->withCustomProperties([
                'height' => $height,
                'duration' => $duration,
            ])
            ->preservingOriginal()
            ->toMediaCollection('videos');
    }

    private function getVideoInfo(mixed $file): array
    {
        $probe = FFProbe::create();
        if (!$probe->isValid($file)) {
            throw new \RuntimeException("$file file is not a valid video");
        }

        $video = $probe->streams($file)
            ->videos()
            ->first();

        if ($video === null) {
            throw new \RuntimeException("No valid video found");
        }

        $height =  (int) $video->get('height', 720);
        $duration = (int) round($probe->format($file)->get('duration'));

        if ($duration < 10) {
            throw new \RuntimeException("Video is too short");
        }

        return [$height, $duration];
    }

    private function parseFileName(array $fileInfo): string
    {
        return str($fileInfo['filename'])
            ->replace(
                ['.', '-', '_', '~', '?', ':', ','],
                ' '
            )
            ->replace("'", '')
            ->replace('"', '')
            ->replace('    ', ' ') // Quadruple space
            ->replace('   ', ' ') // Triple space
            ->replace('  ', ' ') // Double space
            ->lower()
            ->title()
            ->toString();
    }

    private function extractTags(array $fileInfo): array
    {
        return str($fileInfo['dirname'])
            ->replace(config('content.data_path'), '')
            ->lower()
            ->explode('/')
            ->map(fn ($tag) => trim($tag))
            ->reject(fn(string $part) => empty($part))
            ->toArray();
    }
}
