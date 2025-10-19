<?php

declare(strict_types=1);

namespace App\Jobs\Tube;

use App\Libraries\Tube\Notifications;
use App\Services\Tube\ExtractThumbnailsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ExtractThumbnailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $mediaId)
    {
        $this->queue = 'thumbs';
        $this->delay = now()->addSeconds(10);
    }

    /**
     * @throws Exception
     */
    public function handle(ExtractThumbnailsService $service): void
    {
        try {
            $service->execute($this->mediaId);
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            $error = "Thumbnail extraction error for Media Id: $this->mediaId: {$e->getMessage()}";
            Log::error($error);
            Notifications::error(self::class, $this->mediaId, $error);

            throw $e;
        }
    }
}
