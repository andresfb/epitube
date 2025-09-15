<?php

namespace App\Jobs;

use App\Models\Content;
use App\Services\ParseTagsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ParseTagsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string|int $contentId,
        private readonly array $fileInfo,
    ) {}

    /**
     * @throws Exception
     */
    public function handle(ParseTagsService $service): void
    {
        try {
            if (is_int($this->contentId)) {
                $query = Content::where('id', $this->contentId);
            } else {
                $query = Content::where('file_hash', $this->contentId);
            }

            $service->execute($query->firstOrFail(), $this->fileInfo);
        } catch (Exception $e) {
            $file = "{$this->fileInfo['dirname']}/{$this->fileInfo['basename']}";
            Log::error("Error parsing tags for $file: {$e->getMessage()}");

            throw $e;
        }
    }
}
