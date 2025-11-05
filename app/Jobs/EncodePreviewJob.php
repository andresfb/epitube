<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Dtos\Tube\PreviewItem;
use App\Libraries\Tube\Notifications;
use App\Services\Tube\EncodePreviewService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class EncodePreviewJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly PreviewItem $item)
    {
        $this->queue = 'encode';
        $this->delay = now()->addSeconds(10);
    }

    /**
     * @throws Exception
     */
    public function handle(EncodePreviewService $service): void
    {
        try {
            $service->execute($this->item);
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            $error = sprintf(
                'Previews generation error for Content Id: %s Media Id: %s: %s',
                $this->item->contentId,
                $this->item->mediaId,
                $e->getMessage());

            Log::error($error);
            Notifications::error(self::class, $this->item->mediaId, $error);

            throw $e;
        }
    }
}
