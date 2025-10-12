<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Libraries\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Models\Tube\Media;
use Illuminate\Console\Command;
use RuntimeException;
use function Laravel\Prompts\text;

abstract class BaseEncodeCommand extends Command
{
    abstract public function handle(): void;

    protected function getContent(int $contentId = 0): Content
    {
        if ($contentId !== 0) {
            return Content::where('id', $contentId)
                ->firstOrFail();
        }

        $contentId = (int) text('Enter Content Id');
        if (blank($contentId)) {
            throw new RuntimeException('Content id cannot be null');
        }

        return Content::where('id', $contentId)->firstOrFail();
    }

    protected function getMedia(Content $content): Media
    {
        $collection = MediaNamesLibrary::videos();
        if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
            $collection = MediaNamesLibrary::transcoded();
        }

        return Media::where('model_id', $content->id)
            ->where('model_type', Content::class)
            ->where('collection_name', $collection)
            ->firstOrFail();
    }
}
