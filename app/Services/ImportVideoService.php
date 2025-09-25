<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\CreateSymLinksAction;
use App\Actions\TranscodeMediaAction;
use App\Dtos\ImportVideoItem;
use App\Dtos\VideoInfoItem;
use App\Libraries\MediaNamesLibrary;
use App\Libraries\TitleParserLibrary;
use App\Models\Category;
use App\Models\Content;
use App\Models\Feed;
use App\Models\MimeType;
use App\Models\Rejected;
use FFMpeg\FFProbe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class ImportVideoService
{
    private array $bandedTags;

    public function __construct(
        private TitleParserLibrary $parserLibrary,
        private TranscodeMediaAction $transcodeAction,
        private CreateSymLinksAction $symLinksAction,
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

        $videoInfo = $this->getVideoInfo($videoItem);
        if (! $videoInfo->status) {
            return;
        }

        if (! $this->validate($videoItem, $videoInfo->duration)) {
            return;
        }

        Log::notice('Saving content');
        DB::transaction(function () use ($videoItem, $fileHash, $fileInfo, $videoInfo): void {
            $needsTranscode = MimeType::needsTranscode($videoItem->MimeType);
            $tile = $this->parserLibrary->parseFileName($fileInfo)->title()->toString();
            $category = $this->parserLibrary->getRootDirectory() === Config::string('constants.alt_category')
                ? Config::string('constants.alt_category')
                : Config::string('constants.main_category');

            $content = Content::create([
                'category_id' => Category::getId($category),
                'item_id' => $videoItem->Id,
                'file_hash' => $fileHash,
                'title' => $tile,
                'active' => true,
                'og_path' => $videoItem->Path,
                'added_at' => Carbon::parse(filemtime($videoItem->Path)),
            ]);

            $this->parseTags($content, $fileInfo);

            Log::notice('Adding media');
            $media = $content->addMedia($videoItem->Path)
                ->withCustomProperties([
                    'width' => $videoInfo->width,
                    'height' => $videoInfo->height,
                    'duration' => $videoInfo->duration,
                    'is_video' => true,
                    'transcode' => $needsTranscode,
                    'og_path' => $videoItem->Path,
                ])
                ->preservingOriginal()
                ->toMediaCollection(MediaNamesLibrary::videos());

            $this->symLinksAction->handle($media);
            $this->transcodeAction->handle($media);
        });

        Log::notice('Done importing video');
    }

    private function getVideoInfo(ImportVideoItem $videoItem): VideoInfoItem
    {
        if ($videoItem->RunTimeTicks > 0 && $videoItem->Width > 0 && $videoItem->Height > 0) {
            return new VideoInfoItem(
                status: true,
                width: $videoItem->Width,
                height: $videoItem->Height,
                duration: (int) floor($videoItem->RunTimeTicks / 10000000),
            );
        }

        $probe = FFProbe::create();
        if (! $probe->isValid($videoItem->Path)) {
            $message = "$videoItem->Path file is not a valid video";
            $this->createRejected($videoItem, $message);
            Log::error($message);

            return new VideoInfoItem(false);
        }

        $video = $probe->streams($videoItem->Path)
            ->videos()
            ->first();

        if ($video === null) {
            $message = 'No valid video found';
            $this->createRejected($videoItem, $message);
            Log::error($message);

            return new VideoInfoItem(false);
        }

        $height = (int) $video->get('height', 720);
        $width = (int) $video->get('width', 720);
        $duration = (int) $probe->format($videoItem->Path)->get('duration');

        return new VideoInfoItem(
            status: true,
            width: $width,
            height: $height,
            duration: $duration,
        );
    }

    private function parseTags(Content $content, array $fileInfo): void
    {
        $tags = $this->extractTags($fileInfo);
        if (blank($tags)) {
            return;
        }

        $content->attachTags($tags);
        $content->searchableSync();
        Feed::updateIfExists($content);
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
            ->map(fn (string $tag): string => trim($tag))
            ->reject(fn (string $part): bool => empty($part));

        $tags = collect();
        foreach ($sections as $section) {
            $tags = $tags->merge(
                str($section)->explode(' ')
                    ->map(fn (string $tag): string => trim($tag))
                    ->reject(fn (string $part): bool => blank($part)
                        || in_array($part, $this->bandedTags, true)
                        || mb_strlen($part) <= 2)
            );
        }

        return $tags->toArray();
    }

    private function validate(ImportVideoItem $videoItem, int $duration): bool
    {
        if ($duration < Config::integer('content.minimum_duration')) {
            $message = sprintf(
                "The video duration is too short: %s minutes",
                number_format($duration / 60, 2)
            );

            $this->createRejected($videoItem, $message);
            Log::error($message);

            return false;
        }

        $extensions = MimeType::extensions();
        $fileInfo = pathinfo($videoItem->Path);
        if (! in_array($fileInfo['extension'], $extensions, true)) {
            $message = sprintf(
                "File extension: %s is not supported",
                $fileInfo['extension']
            );

            $this->createRejected($videoItem, $message);
            Log::error($message);

            return false;
        }

        return true;
    }

    private function createRejected(ImportVideoItem $videoItem, string $message): void
    {
        Rejected::updateOrCreate([
            'item_id' => $videoItem->Id,
        ], [
            'og_path' => $videoItem->Path,
            'reason' => $message,
        ]);
    }
}
