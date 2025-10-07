<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Libraries\Notifications;
use App\Services\ExtractThumbnailsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ExtractThumbnailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $failOnTimeout = true;

    public function __construct(private readonly int $mediaId)
    {
        $this->queue = 'encode';
        $this->delay = now()->addSeconds(30);
    }

    /**
     * @throws Exception
     */
    public function handle(ExtractThumbnailsService $service): void
    {
        try {
            $service->execute($this->mediaId);
        } catch (Exception $e) {
            $error = "Thumbnail extraction error for Media Id: {$this->mediaId}: {$e->getMessage()}";
            Log::error($error);
            Notifications::error(self::class, $this->mediaId, $error);

            throw $e;
        }
    }
}
