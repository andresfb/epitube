<?php

namespace App\Services\Tube;

use App\Dtos\Tube\PreviewItem;
use App\Exceptions\ProcessRunningException;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Models\Tube\Content;
use App\Traits\Screenable;
use Exception;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class EncodePreviewService extends BaseEncodeService
{
    use Screenable;

    public function setToScreen(bool $toScreen): self
    {
        $this->toScreen = $toScreen;
        $this->videoLibrary->setToScreen($toScreen);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function execute(PreviewItem $item): void
    {
        try {
            $this->prepare($item->mediaId, "$item->size:$item->extension");
        } catch (ProcessRunningException $ex) {
            $this->error($ex->getMessage());

            return;
        }

        try {
            $this->notice(sprintf(
                "Start creating Preview for Content %s, Media: %s, Size: %s, Extension: %s",
                $item->contentId,
                $item->mediaId,
                $item->size,
                $item->extension
            ));

            $content = Content::where('id', $item->contentId)
                ->firstOrFail();

            $file = $this->createClipFile($item);
            $fullPath = Storage::disk($item->processingDisk)
                ->path($file);

            $content->addMedia($fullPath)
                ->withCustomProperties([
                    'size' => $item->size,
                    'extension' => $item->extension,
                    'is_video' => true,
                ])
                ->toMediaCollection(MediaNamesLibrary::previews());

            $this->notice(sprintf(
                "Done creating Preview for Content %s, Media: %s, Size: %s, Extension: %s",
                $item->contentId,
                $item->mediaId,
                $item->size,
                $item->extension
            ));
        } catch (Exception $e) {
            $this->error($e->getMessage());

            throw $e;
        } finally {
            $this->deleteFlag($item->processingDisk);
        }
    }

    private function createClipFile(PreviewItem $item): string
    {
        $fileTemplate = sprintf(
            '%s/preview_%s_%s%s.%s',
            $item->tempPath,
            $item->size,
            $item->extension,
            '%s',
            $item->extension
        );

        $tmpFileTemplate = sprintf($fileTemplate, '_%s');
        $outputFile = sprintf($fileTemplate, '');

        $this->notice("Encoding $outputFile file");

        $tmpFiles = [];
        foreach ($item->sections as $section) {
            $video = FFMpeg::fromDisk($item->downloadDisk)
                ->open($item->relativeVideoPath);

            $tmpFile = sprintf(
                $tmpFileTemplate,
                mb_str_pad($section['index'], 2, '0', STR_PAD_LEFT)
            );

            $video->export()
                ->addFilter(function (VideoFilters $filters) use ($section): void {
                    $filters->clip(
                        TimeCode::fromSeconds($section['start']),
                        TimeCode::fromSeconds($section['duration']),
                    );
                })
                ->addFilter('-crf', 15)
                ->addFilter('-an')
                ->addFilter(function (VideoFilters $filters) use ($item): void {
                    $filters->custom("fps=10,scale=-2:$item->size:flags=lanczos");
                })
                ->toDisk($item->processingDisk)
                ->inFormat($this->getEncodeFormat($item->extension, $item->bitRate))
                ->save($tmpFile);

            $tmpFiles[] = $tmpFile;

            unset($video);
        }

        FFMpeg::fromDisk($item->processingDisk)
            ->open($tmpFiles)
            ->export()
            ->concatWithoutTranscoding()
            ->save($outputFile);

        Storage::disk($item->processingDisk)
            ->delete($tmpFiles);

        return $outputFile;
    }

    private function getEncodeFormat(string $extension, int $bitRate): WebM|X264
    {
        $format = $extension === 'mp4'
            ? new X264('libmp3lame')
            : new WebM('libvorbis');

        return $format->setKiloBitrate($bitRate);
    }
}
