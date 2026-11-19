<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Jobs\Tube\TranscodeVideoJob;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\MimeType;
use App\Services\Tube\TranscodeVideoService;
use RuntimeException;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

final class TranscodeMediaCommand extends BaseEncodeCommand
{
    protected $signature = 'transcode:content {contentId?}';

    protected $description = 'Transcode Content Video into mp4 format';

    public function __construct(private readonly TranscodeVideoService $service)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            clear();
            intro('Starting Transcoding');

            $contentId = (int) $this->argument('contentId');
            $content = $this->getContent($contentId);

            /** @noinspection NotOptimalIfConditionsInspection */
            if ($content->hasMedia(MediaNamesLibrary::transcoded())
                && ! confirm('Media Already has Transcoded video, Continue?')) {
                return;
            }

            $media = $content->getMedia(MediaNamesLibrary::videos())->first();
            if ($media === null) {
                throw new RuntimeException('Video Media not found');
            }

            $transcodeMineTypes = MimeType::transcode();
            if (! in_array($media->mime_type, $transcodeMineTypes, true)
                && ! confirm('The video does not need Transcoding, Continue?')) {
                return;
            }

            $media->setCustomProperty('transcoded', true);
            if (confirm('Dispatch Job?', false)) {
                TranscodeVideoJob::dispatch($media->id);
                info('Job Dispatched');

                return;
            }

            info('Executing service');
            $this->service->execute($media->id);
        } catch (Throwable $e) {
            error($e->getMessage());
        } finally {
            $this->newLine();
            outro('Done');
        }
    }
}
