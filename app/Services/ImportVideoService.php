<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\TranscodeMediaAction;
use App\Libraries\TitleParserLibrary;
use App\Models\Category;
use App\Models\Content;
use Exception;
use FFMpeg\FFProbe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final readonly class ImportVideoService
{
    public function __construct(
        private TitleParserLibrary $parserLibrary,
        private TranscodeMediaAction $transcodeAction,
    ) {}

    /**
     * @throws Exception
     */
    public function execute(array $fileData): void
    {
        Log::notice("Importing video for file: {$fileData['file']}");

        $fileInfo = pathinfo((string) $fileData['file']);
        $fileHash = File::hash($fileData['file']);

        if (Content::foundFileHash($fileHash)) {
            Log::notice("Video already imported: {$fileData['file']}");

            $this->parseTags(
                Content::where('file_hash', $fileHash)->firstOrFail(),
                $fileInfo
            );

            return;
        }

        $category = $this->parserLibrary->getRootDirectory() === Config::string('constants.alt_category')
            ? Config::string('constants.alt_category')
            : Config::string('constants.main_category');

        $content = Content::create([
            'category_id' => Category::getId($category),
            'name_hash' => $fileData['hash'],
            'file_hash' => $fileHash,
            'title' => $this->parserLibrary->parseFileName($fileInfo)->title()->toString(),
            'active' => true,
            'og_path' => $fileData['file'],
            'added_at' => Carbon::parse(filemtime($fileData['file'])),
        ]);

        $this->parseTags($content, $fileInfo);

        [$width, $height, $duration] = $this->getVideoInfo($fileData['file']);

        $media = $content->addMedia($fileData['file'])
            ->withCustomProperties([
                'width' => $width,
                'height' => $height,
                'duration' => $duration,
                'is_video' => true,
            ])
            ->preservingOriginal()
            ->toMediaCollection('videos');

        $this->transcodeAction->handle($media);

        Log::notice('Done importing video');
    }

    private function getVideoInfo(mixed $file): array
    {
        $probe = FFProbe::create();
        if (! $probe->isValid($file)) {
            throw new RuntimeException("$file file is not a valid video");
        }

        $video = $probe->streams($file)
            ->videos()
            ->first();

        if ($video === null) {
            throw new RuntimeException('No valid video found');
        }

        $height = (int) $video->get('height', 720);
        $width = (int) $video->get('width', 720);
        $duration = (int) round($probe->format($file)->get('duration'));

        if ($duration < 10) {
            throw new RuntimeException('Video is too short');
        }

        return [$width, $height, $duration];
    }

    private function parseTags(Content $content, array $fileInfo): void
    {
        $tags = $this->extractTags($fileInfo);
        if (blank($tags)) {
            return;
        }

        $content->attachTags($tags);
        $content->touch();
    }

    private function extractTags(array $fileInfo): array
    {
        $directory = str($fileInfo['dirname'])
            ->replace(config('content.data_path'), '')
            ->lower();

        $sections = str($this->parserLibrary->replaceWords($directory))
            ->replace('-', ' ')
            ->replace('step', ' ')
            ->replace('    ', ' ')
            ->replace('   ', ' ')
            ->replace('  ', ' ')
            ->explode('/')
            ->map(fn ($tag): string => trim((string) $tag))
            ->reject(fn (string $part): bool => empty($part));

        $tags = collect();
        foreach ($sections as $section) {
            $tags = $tags->merge(
                str($section)->explode(' ')
                    ->map(fn ($tag): string => trim((string) $tag))
                    ->reject(fn (string $part): bool => empty($part))
            );
        }

        return $tags->toArray();
    }
}
