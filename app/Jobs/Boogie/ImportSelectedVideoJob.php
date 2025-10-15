<?php

namespace App\Jobs\Boogie;

use App\Dtos\Boogie\ImportSelectedVideoItem;
use App\Services\Boogie\ImportSelectedVideoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportSelectedVideoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly ImportSelectedVideoItem $item)
    {
        $this->queue = 'ingestor';
        $this->delay = now()->addSeconds(10);
    }

    /**
     * @throws Throwable
     */
    public function handle(ImportSelectedVideoService $service): void
    {
        try {
            $service->execute($this->item);
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
