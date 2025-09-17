<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\CreatePreviewsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class CreatePreviewsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $mediaId)
    {
        $this->queue = 'encode';
        $this->delay = now()->addMinutes(5);
    }

    /**
     * @throws Exception
     */
    public function handle(CreatePreviewsService $service): void
    {
        try {
            $service->execute($this->mediaId);
        } catch (Exception $e) {
            Log::error("Previews generation error for Media Id: {$this->mediaId}: {$e->getMessage()}");

            throw $e;
        }
    }
}
