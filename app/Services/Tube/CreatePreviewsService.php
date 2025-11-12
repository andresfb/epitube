<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Dtos\Tube\PreviewItem;
use App\Jobs\Tube\EncodePreviewJob;
use App\Libraries\Tube\MasterVideoLibrary;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Traits\Screenable;
use Exception;
use Illuminate\Support\Facades\Config;

final class CreatePreviewsService
{
    use Screenable;

    public function __construct(
        private readonly MasterVideoLibrary $videoLibrary,
        private readonly EncodePreviewService $encodeService,
    ) {}

    public function setToScreen(bool $toScreen): self
    {
        $this->toScreen = $toScreen;
        $this->encodeService->setToScreen($toScreen);
        $this->videoLibrary->setToScreen($toScreen);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function execute(int $mediaId): void
    {
        $this->videoLibrary->setMediaId($mediaId)
            ->loadVideoInfo();

        $sections = $this->calculateSections($this->videoLibrary->getDuration());
        $this->notice("Starting queuing Preview videos for: $mediaId");

        foreach (Config::array('content.preview_options.sizes') as $size => $bitRate) {
            $size = (int) $size;
            $bitRate = (int) $bitRate;

            if ($this->videoLibrary->getHeight() < $size) {
                continue;
            }

            foreach (Config::array('content.preview_options.extensions') as $extension) {
                EncodePreviewJob::dispatch(
                    $this->loadPreviewItem(
                        $this->videoLibrary->getContent()->id,
                        $size,
                        $bitRate,
                        $extension,
                        $sections
                    )
                );
            }
        }

        $this->notice('Done queuing Preview videos');
    }

    public function generateMissing(int $contentId): void
    {
        $this->notice("Looking for missing Preview videos\n");

        $content = Content::where('id', $contentId)
            ->firstOrFail();

        $collection = MediaNamesLibrary::videos();
        if ($content->hasMedia(MediaNamesLibrary::transcoded())) {
            $collection = MediaNamesLibrary::transcoded();
        }

        $media = $content->getMedia($collection)->firstOrFail();
        $previews = $content->getMedia(MediaNamesLibrary::previews());
        $this->videoLibrary->setMediaId($media->id)
            ->loadVideoInfo();

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
                    if ($preview->getCustomProperty('extension') !== $extension) {
                        continue;
                    }

                    if ($preview->getCustomProperty('size') !== $size) {
                        continue;
                    }

                    $found = true;
                    break;
                }

                if ($found) {
                    $this->notice("Content already has a $size, $extension");

                    continue;
                }

                $this->warning("\nPreview not found. Creating $size, $extension");

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
                } catch (Exception) {
                    continue;
                }
            }
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
        int $size,
        int $bitRate,
        mixed $extension,
        array $sections): PreviewItem
    {
        return new PreviewItem(
            contentId: $contentId,
            mediaId: $this->videoLibrary->getMedia()->id,
            size: $size,
            bitRate: $bitRate,
            extension: $extension,
            sections: $sections,
        );
    }
}
