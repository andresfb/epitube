<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Actions\CreateSymLinksAction;
use App\Actions\TranscodeMediaAction;
use App\Dtos\Tube\ImportVideoItem;
use App\Dtos\Tube\VideoInfoItem;
use App\Enums\SpecialTagType;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Libraries\Tube\TitleParserLibrary;
use App\Models\Tube\Category;
use App\Models\Tube\Content;
use App\Models\Tube\MimeType;
use App\Models\Tube\Rejected;
use App\Models\Tube\SharedTag;
use App\Models\Tube\SpecialTag;
use App\Traits\DirectoryChecker;
use App\Traits\TagsProcessor;
use App\Traits\VideoValidator;
use FFMpeg\FFProbe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

final class ImportVideoService
{
    use DirectoryChecker;
    use TagsProcessor;
    use VideoValidator;

    public function __construct(
        private readonly TitleParserLibrary   $parserLibrary,
        private readonly TranscodeMediaAction $transcodeAction,
        private readonly CreateSymLinksAction $symLinksAction,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(ImportVideoItem $videoItem): int
    {
        Log::notice("Importing video for file: $videoItem->Path");

        $fileInfo = pathinfo($videoItem->Path);
        $fileHash = File::hash($videoItem->Path);

        if (Content::isDifferentFileVersion($fileHash, $videoItem->Path)) {
            Log::notice("Video already imported: $videoItem->Path");

            $this->parseTags(
                Content::where('file_hash', $fileHash)->firstOrFail(),
                $fileInfo
            );

            return 0;
        }

        $videoInfo = $this->getVideoInfo($videoItem);
        if (! $videoInfo->status) {
            return 0;
        }

        $videoItem = $videoItem->withVideoInfo($videoInfo);
        if (! $this->validate($videoItem)) {
            return 0;
        }

        Log::notice('Saving content');
        $media = DB::transaction(function () use ($videoItem, $fileHash, $fileInfo, $videoInfo): Media {
            if ($videoItem->FromDownload) {
                $fileInfo['filename'] = $videoItem->Name;
            }

            $tile = $this->deTitle(
                $this->parserLibrary->parseFileName($fileInfo)->title()
            );

            $category = $this->parserLibrary->getRootDirectory() === Config::string('constants.alt_category')
                ? Config::string('constants.alt_category')
                : Config::string('constants.main_category');

            $content = Content::updateOrCreate([
                'item_id' => $videoItem->Id,
            ],[
                'category_id' => Category::getId($category),
                'file_hash' => $fileHash,
                'title' => $tile,
                'active' => true,
                'og_path' => $videoItem->Path,
                'added_at' => Carbon::parse(filemtime($videoItem->Path)),
            ]);

            $this->parseTags($content, $fileInfo);

            Log::notice('Adding media');
            $needsTranscode = MimeType::needsTranscode($videoItem->MimeType);
            $media = $content->addMedia($videoItem->Path)
                ->withCustomProperties([
                    'width' => $videoInfo->width,
                    'height' => $videoInfo->height,
                    'duration' => $videoInfo->duration,
                    'is_video' => true,
                    'transcoded' => $needsTranscode,
                    'og_path' => $videoItem->Path,
                ])
                ->preservingOriginal()
                ->toMediaCollection(MediaNamesLibrary::videos());

            $this->symLinksAction->handle($media);

            return $media;
        });

        $this->transcodeAction->handle($media);
        Log::notice('Done importing video');

        return $media->model_id;
    }

    public function parseTags(Content $content, array $fileInfo): void
    {
        Log::notice('Parsing tags');
        $tags = $this->extractTags($fileInfo);
        if (blank($tags)) {
            return;
        }

        $content->attachTags($tags, 'main');
    }

    public function extractTags(array $fileInfo): array
    {
        $directory = str($fileInfo['dirname'])
            ->replace(config('content.data_path'), '')
            ->replace(config('selected-videos.download_path'), '')
            ->lower();

        $tags = collect();
        $sharedTags = SharedTag::getList();
        $bandedTags = SpecialTag::getList(SpecialTagType::BANDED);

        str($directory)
            ->replace("'", '')
            ->replace('step', ' ')
            ->replace('    ', ' ')
            ->replace('   ', ' ')
            ->replace('  ', ' ')
            ->explode('/')
            ->map(fn (string $text): string => mb_trim($text))
            ->reject(function (string $text) use($bandedTags): bool {
                return blank($text) || in_array($text, $bandedTags, true);
            })
            ->each(function (string $text) use (&$tags, $sharedTags) {
                if ($this->isHash($text)) {
                    return;
                }

                str($text)
                    ->explode(' - ')
                    ->map(fn (string $text): string => mb_trim($text))
                    ->reject(fn (string $text): bool => blank($text))
                    ->each(function (string $text) use (&$tags, $sharedTags) {
                        $this->collectTags($text, $tags, $sharedTags);
                    });
            });

        if (blank($this->parserLibrary->getExtraTags())) {
            return $tags->toArray();
        }

        foreach ($this->parserLibrary->getExtraTags() as $tag) {
            if ($tags->contains($tag)) {
                continue;
            }

            $tags->push($tag);
        }

        return $tags->toArray();
    }

    private function getVideoInfo(ImportVideoItem $videoItem): VideoInfoItem
    {
        if ($videoItem->Duration > 0 && $videoItem->Width > 0 && $videoItem->Height > 0) {
            return new VideoInfoItem(
                status: true,
                width: $videoItem->Width,
                height: $videoItem->Height,
                duration: $videoItem->Duration,
            );
        }

        $probe = FFProbe::create();
        if (! $probe->isValid($videoItem->Path)) {
            $message = "$videoItem->Path file is not a valid video";
            Rejected::reject($videoItem, $message);
            Log::error($message);

            return new VideoInfoItem(false);
        }

        $video = $probe->streams($videoItem->Path)
            ->videos()
            ->first();

        if ($video === null) {
            $message = 'No valid video found';
            Rejected::reject($videoItem, $message);
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
}
