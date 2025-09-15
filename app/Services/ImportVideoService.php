<?php

namespace App\Services;

use App\Actions\TranscodeMediaAction;
use App\Jobs\ExtractThumbnailJob;
use App\Jobs\ParseTagsJob;
use App\Libraries\TitleParserLibrary;
use App\Models\Content;
use Exception;
use FFMpeg\FFProbe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use RuntimeException;

readonly class ImportVideoService
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
        $fileInfo = pathinfo($fileData['file']);
        $fileHash = hash_file('md5', $fileData['file']);

        if (Content::found($fileHash)) {
            ParseTagsJob::dispatch($fileHash, $fileInfo);

            return;
        }

        [$width, $height, $duration] = $this->getVideoInfo($fileData['file']);
        $fullName = $this->parserLibrary->parseFileName($fileInfo);

        $content = Content::create([
            'name_hash' => $fileData['hash'],
            'file_hash' => $fileHash,
            'title' => Str::of($fullName)->title()->toString(),
            'active' => true,
            'og_path' => $fileData['file'],
            'added_at' => Carbon::parse(filemtime($fileData['file'])),
        ]);

        ParseTagsJob::dispatch($content->id, $fileInfo);

        $content->attachTaxonomies([
            Str::of(Config::string('constants.main_category'))
                ->slug()
        ]);

        $media = $content->addMedia($fileData['file'])
            ->withCustomProperties([
                'width' => $width,
                'height' => $height,
                'duration' => $duration,
            ])
            ->preservingOriginal()
            ->toMediaCollection('videos');

        $this->transcodeAction->handle($media);
        $this->processThumbnail($content, $fileData['file']);
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
            throw new RuntimeException("No valid video found");
        }

        $height =  (int) $video->get('height', 720);
        $width = (int) $video->get('width', 720);
        $duration = (int) round($probe->format($file)->get('duration'));

        if ($duration < 10) {
            throw new RuntimeException("Video is too short");
        }

        return [$width, $height, $duration];
    }

    /**
     * @throws Exception
     */
    private function processThumbnail(Content $content, string $file): void
    {
        $fileInfo = pathinfo($file);
        $image = Str::of($file)
            ->replace($fileInfo['extension'], 'jpg')
            ->toString();

        if (! file_exists($image)) {
            ExtractThumbnailJob::dispatch($content->id)
                ->onQueue('encode')
                ->delay(now()->addSeconds(10));

            return;
        }

        $content->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection('thumbnail');
    }
}
