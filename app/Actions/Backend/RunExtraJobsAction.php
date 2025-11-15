<?php

declare(strict_types=1);

namespace App\Actions\Backend;

use App\Jobs\Tube\CreatePreviewsJob;
use App\Jobs\Tube\ExtractThumbnailsJob;
use App\Jobs\Tube\GenerateDownscalesJob;
use App\Jobs\Tube\SearchableWordsJob;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final readonly class RunExtraJobsAction
{
    public function handle(int $mediaId): void
    {
        if (! Config::boolean('constants.enable_encode_jobs')) {
            Log::notice('@RunExtraJobsAction.handle: Encode jobs disabled.');

            return;
        }

        SearchableWordsJob::dispatch($mediaId);

        ExtractThumbnailsJob::dispatch($mediaId);

        CreatePreviewsJob::dispatch($mediaId);

        GenerateDownscalesJob::dispatch($mediaId);
    }
}
