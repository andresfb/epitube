<?php

namespace App\Jobs\Tube;

use App\Services\Tube\DeleteDisabledService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteDisabledJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->queue = 'media';
        $this->delay = now()->addSeconds(10);
    }

    /**
     * @throws Exception
     */
    public function handle(DeleteDisabledService $service): void
    {
        try {
            $service->execute();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
