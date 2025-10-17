<?php

namespace App\Traits;

use App\Dtos\Tube\ImportVideoItem;
use App\Models\Tube\MimeType;
use App\Models\Tube\Rejected;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

trait VideoValidator
{
    private array $videoExtensions = [];

    private function getExtensions(): array
    {
        if (! blank($this->videoExtensions)) {
            return $this->videoExtensions;
        }

        $this->videoExtensions = MimeType::extensions();

        return $this->videoExtensions;
    }

    private function validate(ImportVideoItem $videoItem): bool
    {
        $fileInfo = pathinfo($videoItem->Path);
        $extension = mb_strtolower(mb_trim($fileInfo['extension']));
        if (! in_array($extension, $this->getExtensions(), true)) {
            $message = sprintf(
                "File extension: %s is not supported",
                $extension
            );

            Rejected::reject($videoItem, $message);
            Log::error($message);

            return false;
        }

        if ($videoItem->Duration > 0 && $videoItem->Duration < Config::integer('content.minimum_duration')) {
            $message = "The video duration is too short: $videoItem->Duration seconds";
            Rejected::reject($videoItem, $message);
            Log::error($message);

            return false;
        }

        if ($videoItem->Height === 0 || $videoItem->Width === 0) {
            return true;
        }

        if ($videoItem->Height > $videoItem->Width) {
            $message = 'Vertical videos are not allowed';
            Rejected::reject($videoItem, $message);
            Log::error($message);

            return false;
        }

        return true;
    }
}
