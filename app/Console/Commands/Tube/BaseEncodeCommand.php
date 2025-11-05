<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Models\Tube\Content;
use App\Traits\MediaGetter;
use Illuminate\Console\Command;
use RuntimeException;
use function Laravel\Prompts\text;

abstract class BaseEncodeCommand extends Command
{
    use MediaGetter;

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
}
