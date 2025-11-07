<?php

declare(strict_types=1);

namespace App\Jobs\Tube;

use App\Actions\Backend\VideoProgressAction;
use App\Dtos\Tube\VideoProgressItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class VideoProgressJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $slug,
        private readonly VideoProgressItem $item
    ) {
        $this->queue = 'default';
    }

    /**
     * @throws Throwable
     */
    public function handle(VideoProgressAction $action): void
    {
        try {
            $action->handle($this->slug, $this->item);
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
