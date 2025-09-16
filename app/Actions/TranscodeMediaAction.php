<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\TranscodeVideoJob;
use App\Models\Media;
use App\Models\MimeType;
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
            $transcodeMineTypes = MimeType::transcode();

            if (! in_array($media->mime_type, $transcodeMineTypes, true)) {
                Log::notice("Media: $media->id of type $media->mime_type doesn't need transcoding");
                $this->jobsAction->handle($media->id);

                return;
            }

            Log::info("Transcoding $media->model_id | $media->name");

            if (! Config::boolean('constants.enable_encode_jobs')) {
                Log::notice('@TranscodeMediaAction.handle: Encode jobs disabled.');

                return;
            }

            $media->setCustomProperty('transcode', true);

            TranscodeVideoJob::dispatch($media->id);
        } catch (Exception $e) {
            Log::error("Error transcoding Media Id: $media->id: {$e->getMessage()}");

            throw $e;
        }
    }
}
