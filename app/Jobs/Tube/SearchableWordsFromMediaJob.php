<?php

namespace App\Jobs\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Media;
use App\Services\Tube\SearchableWordsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SearchableWordsFromMediaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $mediaId)
    {
        $this->queue = 'default';
    }

    /**
     * @throws Exception
     */
    public function handle(SearchableWordsService $service): void
    {
        try {
            $media = Media::query()
                ->where('id', $this->mediaId)
                ->firstOrFail();

            $service->execute($media->model);
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
