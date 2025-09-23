<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\TranscodeMediaAction;
use App\Dtos\ImportVideoItem;
use App\Libraries\MediaNamesLibrary;
use App\Libraries\TitleParserLibrary;
use App\Models\Category;
use App\Models\Content;
use Exception;
use FFMpeg\FFProbe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final readonly class ImportVideoService
{
    private array $bandedTags;

    public function __construct(
        private TitleParserLibrary $parserLibrary,
        private TranscodeMediaAction $transcodeAction,
    ) {
        $this->bandedTags = Config::array('content.banded_tags');
    }

    /**
     * @throws Throwable
     */
    public function execute(ImportVideoItem $videoItem): void
    {
        Log::notice("Importing video for file: $videoItem->Path");

        $fileInfo = pathinfo($videoItem->Path);
        $fileHash = File::hash($videoItem->Path);

        if (Content::fileHashExists($fileHash)) {
            Log::notice("Video already imported: $videoItem->Path");

            $this->parseTags(
                Content::where('file_hash', $fileHash)->firstOrFail(),
                $fileInfo
            );

            return;
        }

        $category = $this->parserLibrary->getRootDirectory() === Config::string('constants.alt_category')
            ? Config::string('constants.alt_category')
            : Config::string('constants.main_category');

        Log::notice('Saving content');

        DB::transaction(function () use ($videoItem, $category, $fileHash, $fileInfo) {
            $content = Content::create([
                'category_id' => Category::getId($category),
                'item_id' => $videoItem->Id,
                'file_hash' => $fileHash,
                'title' => $this->parserLibrary->parseFileName($fileInfo)->title()->toString(),
                'active' => true,
                'og_path' => $videoItem->Path,
                'added_at' => Carbon::parse(filemtime($videoItem->Path)),
            ]);

            $this->parseTags($content, $fileInfo);

            [$width, $height, $duration] = $this->getVideoInfo($videoItem);

            Log::notice('Adding media');
            $media = $content->addMedia($videoItem->Path)
                ->withCustomProperties([
                    'width' => $width,
                    'height' => $height,
                    'duration' => $duration,
                    'is_video' => true,
                ])
                ->preservingOriginal()
                ->toMediaCollection(MediaNamesLibrary::videos());

            $this->transcodeAction->handle($media);
        });

        Log::notice('Done importing video');
    }

    private function getVideoInfo(ImportVideoItem $videoItem): array
    {
        if ($videoItem->RunTimeTicks > 0 && $videoItem->Width > 0 && $videoItem->Height > 0) {
            return [
                $videoItem->Width,
                $videoItem->Height,
                (int) floor($videoItem->RunTimeTicks / 10000000),
            ];
        }

        $probe = FFProbe::create();
        if (! $probe->isValid($videoItem->Path)) {
            throw new RuntimeException("$videoItem->Path file is not a valid video");
        }

        $video = $probe->streams($videoItem->Path)
            ->videos()
            ->first();

        if ($video === null) {
            throw new RuntimeException('No valid video found');
        }

        $height = (int) $video->get('height', 720);
        $width = (int) $video->get('width', 720);
        $duration = (int) $probe->format($videoItem->Path)->get('duration');

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
        $content->searchableSync();
    }

    private function extractTags(array $fileInfo): array
    {
        $directory = str($fileInfo['dirname'])
            ->replace(config('content.data_path'), '')
            ->lower();

        $sections = str($this->parserLibrary->replaceWords($directory))
            ->replace("'", '')
            ->replace('-', ' ')
            ->replace('step', ' ')
            ->replace('    ', ' ')
            ->replace('   ', ' ')
            ->replace('  ', ' ')
            ->explode('/')
            ->map(fn ($tag): string => trim($tag))
            ->reject(fn (string $part): bool => empty($part));

        $tags = collect();
        foreach ($sections as $section) {
            $tags = $tags->merge(
                str($section)->explode(' ')
                    ->map(fn ($tag): string => trim($tag))
                    ->reject(function (string $part): bool {
                        return blank($part)
                            || in_array($part, $this->bandedTags, true)
                            || mb_strlen($part) <= 2;
                    })
            );
        }

        return $tags->toArray();
    }
}
