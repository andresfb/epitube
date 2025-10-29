<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Dtos\Tube\PreviewItem;
use App\Exceptions\ProcessRunningException;
use App\Jobs\EncodePreviewJob;
use App\Libraries\Tube\MasterVideoLibrary;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Traits\Screenable;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

final class CreatePreviewsService extends BaseEncodeService
{
    use Screenable;

    private int $mediaId = 0;

    public function __construct(
        MasterVideoLibrary $videoLibrary,
        private readonly EncodePreviewService $encodeService,
    ) {
        parent::__construct($videoLibrary);
    }

    /**
     * @throws Exception
     */
    public function execute(int $mediaId): void
    {
        $this->mediaId = $mediaId;

        $this->notice("Starting creating Preview videos for: $this->mediaId");

        try {
            $this->generate($this->videoLibrary->getContent());

            $this->notice('Done queuing Preview videos');
        } finally {
            $this->videoLibrary->deleteTempFiles();

            $this->deleteFlag($this->videoLibrary->getProcessingDisk());
        }
    }

    public function generateMissing(int $contentId): void
    {
        $this->notice('Looking for missing Preview videos');

        $content = Content::where('id', $contentId)
            ->firstOrFail();

        $collection = MediaNamesLibrary::videos();
        if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
            $collection = MediaNamesLibrary::transcoded();
        }

        $media = $content->getMedia($collection)->firstOrFail();
        $previews = $content->getMedia(MediaNamesLibrary::previews());

        $this->videoLibrary->loadVideoInfo($media->id);
        $sections = $this->calculateSections($this->videoLibrary->getDuration());

        foreach (Config::array('content.preview_options.sizes') as $size => $bitRate) {
            $size = (int) $size;
            $bitRate = (int) $bitRate;

            if ($this->videoLibrary->getHeight() < $size) {
                continue;
            }

            foreach (Config::array('content.preview_options.extensions') as $extension) {
                $found = false;

                $this->notice("Looking for $size, $extension Preview");

                foreach ($previews as $preview) {
                    if ($preview->getCustomProperty('extension') !== $extension
                        || $preview->getCustomProperty('size') !== $size
                    ) {
                        continue;
                    }

                    $found = true;
                    break;
                }

                if ($found) {
                    $this->notice("Content already has a $size, $extension Preview");

                    continue;
                }

                 $this->notice("Preview not found. Creating $size, $extension");

                try {
                    $this->prepare($this->mediaId, "$size:$extension");
                } catch (ProcessRunningException $exception) {
                    $this->error($exception->getMessage());

                    continue;
                }

                try {
                    $this->encodeService->execute(
                        $this->loadPreviewItem(
                            $content->id,
                            $size,
                            $bitRate,
                            $extension,
                            $sections
                        )
                    );
                } catch (Exception $e) {
                    $this->error($e->getMessage());

                    continue;
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    private function generate(Content $content): void
    {
        try {
            $this->notice('Generating Preview videos');
            $this->videoLibrary->loadVideoInfo($this->mediaId);
            $sections = $this->calculateSections($this->videoLibrary->getDuration());

            foreach (Config::array('content.preview_options.sizes') as $size => $bitRate) {
                $size = (int) $size;
                $bitRate = (int) $bitRate;

                if ($this->videoLibrary->getHeight() < $size) {
                    continue;
                }

                foreach (Config::array('content.preview_options.extensions') as $extension) {
                    try {
                        $this->prepare($this->mediaId, "$size:$extension");
                    } catch (ProcessRunningException $exception) {
                        $this->error($exception->getMessage());

                        continue;
                    }

                    EncodePreviewJob::dispatch(
                        $this->loadPreviewItem(
                            $content->id,
                            $size,
                            $bitRate,
                            $extension,
                            $sections
                        )
                    );
                }
            }
        } catch (Exception $e) {
            File::deleteDirectory($this->videoLibrary->getProcessingPath());
            $content->getMedia(MediaNamesLibrary::previews())
                ->each(function ($media): void {
                    $media->forceDelete();
                });

            throw $e;
        }
    }

    private function calculateSections(int $duration): array
    {
        $trimmedDuration = $duration
            - ($duration * (Config::integer('content.preview_options.padding_time') / 100));
        $sectionDuration = Config::integer('content.preview_options.section_length');
        $sectionsPerInterval = Config::integer('content.preview_options.sections');
        $maxPreviewLength = Config::integer('content.preview_options.max_preview_length');
        $intervalDuration = 20 * 60; // 20 minutes in seconds

        // Calculate maximum number of sections based on max preview length
        $maxSections = (int) floor($maxPreviewLength / $sectionDuration);

        // Calculate the total number of sections needed
        $totalSections = min(
            $maxSections,
            max(
                $sectionsPerInterval, // minimum 3 sections
                ceil($trimmedDuration / $intervalDuration) * $sectionsPerInterval
            )
        );

        // Calculate spacing between sections
        $availableDuration = $trimmedDuration - ($totalSections * $sectionDuration);
        $spacing = $availableDuration / ($totalSections + 1);

        $sections = [];
        for ($i = 0; $i < $totalSections; $i++) {
            $index = $i + 1;
            $startTime = $index * $spacing + ($i * $sectionDuration);

            // Ensure the section fits within the video duration
            if ($startTime + $sectionDuration <= $trimmedDuration) {
                $sections[] = [
                    'index' => (string) $index,
                    'start' => $startTime,
                    'duration' => $sectionDuration,
                ];
            }
        }

        return $sections;
    }

    private function loadPreviewItem(
        int $contentId,
        int     $size,
        int     $bitRate,
        mixed   $extension,
        array   $sections): PreviewItem
    {
        return new PreviewItem(
            contentId: $contentId,
            mediaId: $this->mediaId,
            size: $size,
            bitRate: $bitRate,
            extension: $extension,
            sections: $sections,
            tempPath: $this->videoLibrary->getTempPath(),
            downloadDisk: $this->videoLibrary->getDownloadDisk(),
            processingDisk: $this->videoLibrary->getProcessingDisk(),
            relativeVideoPath: $this->videoLibrary->getRelativeVideoPath(),
        );
    }
}
