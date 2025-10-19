<?php

declare(strict_types=1);

namespace App\Jobs\Tube;

use App\Services\Tube\CreateFeedService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final class CreateFeedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private readonly int $maxRuns;

    public function __construct(private readonly bool $fromRequest = false)
    {
        $this->queue = 'default';
        $this->maxRuns = Config::integer('feed.max_feed_runs');
    }

    /**
     * @throws Exception
     */
    public function handle(CreateFeedService $service): void
    {
        try {
            if (Config::string('queue.default') === 'sync' || app()->isLocal()) {
                Log::warning('Cannot run Feed Job on this environment');

                return;
            }

            if ($this->hasTooManyRuns()) {
                Log::warning('CreateFeedJob ran too many times today');

                return;
            }

            $service->execute();
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }

    private function hasTooManyRuns(): bool
    {
        if (! $this->fromRequest) {
            return false;
        }

        $key = md5(__CLASS__);
        if (! Cache::has($key)) {
            Cache::put($key, 1, now()->endOfDay());

            return false;
        }

        $runs = (int) Cache::get($key);
        if ($runs >= $this->maxRuns) {
            return true;
        }

        Cache::increment($key);

        return false;
    }
}
