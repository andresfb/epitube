<?php

namespace App\Services\Boogie;

use App\Dtos\Boogie\ImportSelectedVideoItem;
use App\Dtos\Tube\ImportVideoItem;
use App\Libraries\Tube\TitleParserLibrary;
use App\Models\Boogie\SelectedVideo;
use App\Models\Tube\Content;
use App\Models\Tube\MimeType;
use App\Services\Tube\ImportVideoService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class ImportSelectedVideoService
{
    public function __construct(
        private ImportVideoService $importService,
        private TitleParserLibrary $titleParser,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(ImportSelectedVideoItem $item): void
    {
        $files = File::files($item->downloadPath);
        if (blank($files)) {
            Log::error("No files found on $item->downloadPath");

            return;
        }

        $filePath = '';
        foreach ($files as $file) {
            if (! in_array($file->getExtension(), MimeType::extensions(), true)) {
                continue;
            }

            if ($file->getSize() < 8192) {
                continue;
            }

            $filePath = $file->getPathname();
            break;
        }

        if (blank($filePath)) {
            Log::error("No video found on $item->downloadPath");

            return;
        }

        $selectedVideo = SelectedVideo::query()
            ->with('parent')
            ->where('id', $item->videoId)
            ->firstOrFail();

        $importItem = new ImportVideoItem(
            Id: $selectedVideo->hash,
            Name: $selectedVideo->title,
            Path: $filePath,
            MimeType: mime_content_type($filePath),
            FromDownload: true,
        );

        $contentId = $this->importService->execute($importItem);
        if (blank($contentId)) {
            Log::error("Import video $selectedVideo->id | $selectedVideo->title failed");

            return;
        }

        Log::notice('Adding extra tags to the Content...');
        if (blank($selectedVideo->parent->raw_tags)) {
            Log::warning('No extra tags found');

            return;
        }

        $tags = $this->titleParser->removeWords(
            $this->titleParser->replaceWords(
                mb_strtolower($selectedVideo->parent->raw_tags)
            ))
            ->replace("'", '')
            ->replace('    ', ' ')
            ->replace('   ', ' ')
            ->replace('  ', ' ')
            ->replace(',,',',')
            ->rtrim(',')
            ->ltrim(',')
            ->explode(',')
            ->map(fn($tag): string => str($tag)
                ->trim()
                ->replace('-', ' ')
                ->title()
                ->trim()
                ->toString()
            )
            ->reject(fn(string $tag): bool => blank($tag) || is_numeric($tag));

        $selects = 5;
        if ($tags->count() < $selects) {
            $selects = $tags->count();
        }

        $tags = $tags->random($selects)
            ->toArray();

        $content = Content::query()
            ->where('id', $contentId)
            ->firstOrFail();

        $content->attachTags($tags);
        Log::notice('Add extra tags done');
    }
}
