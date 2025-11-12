<?php

declare(strict_types=1);

namespace App\Jobs\Tube;

use App\Dtos\Tube\PreviewItem;
use App\Libraries\Tube\Notifications;
use App\Services\Tube\EncodePreviewService;
use ErrorException;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class EncodePreviewJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $maxRuns = 3;

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
        } catch (ErrorException $e) {
            $this->checkFails();

            Log::error($e->getMessage());
            self::dispatch($this->item)
                ->delay(now()->addMinutes(5));
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

    private function checkFails(): void
    {
        $key = md5("TEMP:DIR:ERROR:{$this->item->mediaId}");

        $count = Cache::get($key, 0);
        if ($count > $this->maxRuns) {
            throw new MaxAttemptsExceededException(
                "Preview Encoder Job for Media Id: {$this->item->mediaId} failed. Max runs reached"
            );
        }

        if (!Cache::has($key)) {
            $count++;
            Cache::put($key, $count, now()->addMinutes(5));

            return;
        }

        Cache::increment($key);
    }
}
