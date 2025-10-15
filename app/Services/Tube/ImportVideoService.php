<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Actions\CreateSymLinksAction;
use App\Actions\TranscodeMediaAction;
use App\Dtos\Tube\ImportVideoItem;
use App\Dtos\Tube\VideoInfoItem;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Libraries\Tube\TitleParserLibrary;
use App\Models\Tube\Category;
use App\Models\Tube\Content;
use App\Models\Tube\MimeType;
use App\Models\Tube\Rejected;
use App\Traits\DirectoryChecker;
use FFMpeg\FFProbe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

final readonly class ImportVideoService
{
    use DirectoryChecker;

    public function __construct(
        private TitleParserLibrary $parserLibrary,
        private TranscodeMediaAction $transcodeAction,
        private CreateSymLinksAction $symLinksAction,
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

            $tile = $this->parserLibrary->parseFileName($fileInfo)->title()->toString();
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
        $sharedTags = Config::array('content.shared_tags');
        $bandedTags = Config::array('content.banded_tags');

        str($directory)
            ->replace("'", '')
            ->replace('step', ' ')
            ->replace('    ', ' ')
            ->replace('   ', ' ')
            ->replace('  ', ' ')
            ->explode('/')
            ->map(fn (string $text): string => trim($text))
            ->reject(function (string $text) use($bandedTags): bool {
                return blank($text) || in_array($text, $bandedTags, true);
            })
            ->each(function (string $text) use (&$tags, $sharedTags) {
                if ($this->isHash($text)) {
                    return;
                }

                str($text)
                    ->explode(' - ')
                    ->map(fn (string $text): string => trim($text))
                    ->reject(fn (string $text): bool => blank($text))
                    ->each(function (string $text) use (&$tags, $sharedTags) {
                        $tag = ucwords($text);
                        $tags->push($tag);

                        if (array_key_exists($tag, $sharedTags)) {
                            foreach ($sharedTags[$tag] as $sharedTag) {
                                $tags->push($sharedTag);
                            }
                        }
                    });
            });

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

    private function validate(ImportVideoItem $videoItem): bool
    {
        if ($videoItem->Height > $videoItem->Width) {
            $message = 'Vertical videos are not allowed';
            Rejected::reject($videoItem, $message);
            Log::error($message);

            return false;
        }

        if ($videoItem->Duration < Config::integer('content.minimum_duration')) {
            $message = "The video duration is too short: $videoItem->Duration seconds";
            Rejected::reject($videoItem, $message);
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

            Rejected::reject($videoItem, $message);
            Log::error($message);

            return false;
        }

        return true;
    }
}
