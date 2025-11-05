<?php

declare(strict_types=1);

namespace App\Libraries\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Media;
use App\Notifications\EncodeErrorNotification;
use Exception;
use Illuminate\Support\Facades\Log;

final class Notifications
{
    public static function error(string $caller, int $mediaId, string $error): void
    {
        try {
            $media = Media::where('id', $mediaId)
                ->firstOrFail();

            $content = Content::where('id', $media->model_id)
                ->firstOrFail();

            $content->notify(new EncodeErrorNotification(
                caller: $caller,
                mediaId: $mediaId,
                ogPath: $content->og_path,
                error: $error,
            ));
        } catch (Exception $e) {
            Log::error(
                "Can't send notification for Media: $mediaId: Message: $error, Caller: $caller, Error: {$e->getMessage()}"
            );
        }
    }
}
