<?php

declare(strict_types=1);

namespace App\Jobs\Tube;

use App\Models\Tube\Content;
use App\Services\Tube\SearchableWordsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SearchableWordsFromContentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $contentId)
    {
        $this->queue = 'default';
    }

    /**
     * @throws Exception
     */
    public function handle(SearchableWordsService $service): void
    {
        try {
            $content = Content::query()
                ->where('id', $this->contentId)
                ->firstOrFail();

            $service->execute($content);
        } catch (MaxAttemptsExceededException $e) {
            Log::error($e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
