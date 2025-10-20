<?php

declare(strict_types=1);

namespace App\Jobs\Tube;

use App\Libraries\Tube\Notifications;
use App\Services\Tube\GenerateDownscalesService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class GenerateDownscalesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $mediaId)
    {
        $this->queue = 'ingestor';
        $this->delay = now()->addSeconds(10);
    }

    /**
     * @throws Exception
     */
    public function handle(GenerateDownscalesService $service): void
    {
        try {
            $service->execute($this->mediaId);
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            $error = "Downscales error for Media Id: $this->mediaId: {$e->getMessage()}";
            Log::error($error);
            Notifications::error(self::class, $this->mediaId, $error);

            throw $e;
        }
    }
}
