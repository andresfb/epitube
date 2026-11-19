<?php

declare(strict_types=1);

namespace App\Jobs\Tube;

use App\Services\Tube\SyncFeedService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SyncFeedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $contentId)
    {
        $this->queue = 'default';
        $this->delay = now()->addSeconds(20);
    }

    /**
     * @throws Exception
     */
    public function handle(SyncFeedService $service): void
    {
        try {
            $service->execute($this->contentId);
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
