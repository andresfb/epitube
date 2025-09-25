<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\CreatePreviewsJob;
use App\Jobs\ExtractThumbnailsJob;
use App\Jobs\GenerateDownscalesJob;
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

        // TODO: add a job to replace the media file in the content disk with a symlink to the actual file

        ExtractThumbnailsJob::dispatch($mediaId);

        CreatePreviewsJob::dispatch($mediaId);

        if (! Config::boolean('constants.enable_downscales')) {
            Log::notice('Downscales not enabled');

            return;
        }

        GenerateDownscalesJob::dispatch($mediaId);
    }
}
