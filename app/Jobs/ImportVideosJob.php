<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ImportVideosService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ImportVideosJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->queue = 'ingestor';
        $this->delay = now()->addSeconds(5);
    }

    /**
     * @throws Exception
     */
    public function handle(ImportVideosService $service): void
    {
        try {
            $service->execute();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
