<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\TranscodeVideoJob;
use App\Libraries\Notifications;
use App\Models\Tube\Media;
use App\Models\Tube\MimeType;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

final readonly class TranscodeMediaAction
{
    public function __construct(private RunExtraJobsAction $jobsAction) {}

    /**
     * @throws Exception
     */
    public function handle(Media $media): void
    {
        try {
            Log::notice("Check if video needs transcoding: $media->id");

            if (! MimeType::needsTranscode($media->mime_type)) {
                Log::notice("Media: $media->id of type $media->mime_type doesn't need transcoding");
                $this->jobsAction->handle($media->id);

                return;
            }

            Log::info("Transcoding $media->model_id | $media->name");

            if (! Config::boolean('constants.enable_encode_jobs')) {
                Log::notice('@TranscodeMediaAction.handle: Encode jobs disabled.');

                return;
            }

            TranscodeVideoJob::dispatch($media->id);
        } catch (Exception $e) {
            $error = "Error checking for transcoding on Media Id: $media->id: {$e->getMessage()}";
            Log::error($error);
            Notifications::error(self::class, $media->id, $error);

            throw $e;
        }
    }
}
