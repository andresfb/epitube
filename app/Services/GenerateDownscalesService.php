<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\EncodeDownscaleJob;
use App\Libraries\MasterVideoLibrary;
use App\Models\Tube\Media;
use App\Models\Tube\MimeType;
use App\Traits\Encodable;
use Exception;
use FFMpeg\FFProbe;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class GenerateDownscalesService
{
    use Encodable;

    public const array RESOLUTIONS = [
        '720p' => 720,
        '1080p' => 1080,
    ];

    public function __construct(private readonly MasterVideoLibrary $videoLibrary) {}

    /**
     * @throws Exception
     */
    public function execute(int $mediaId): void
    {
        Log::notice("Starting generating downscales for: $mediaId");
        $media = Media::where('id', $mediaId)
            ->firstOrFail();

        if (! $this->canConvert($media)) {
            throw new RuntimeException("Media not supported: $media->id");
        }

        $mediaHeight = (int) $media->getCustomProperty('height');
        $minDowRes = Config::integer('content.min_down_res', 1080);

        if ($mediaHeight < $minDowRes) {
            Log::notice(sprintf(
                "Media doesn't need downscaling at %sp resolution",
                $mediaHeight
            ));

            return;
        }

        $this->videoLibrary->downloadMaster($media);
        $resolutions = collect(self::RESOLUTIONS)
            ->filter(fn (int $resolution): bool => $resolution < $mediaHeight);

        foreach ($resolutions as $resolution) {
            Log::notice("Queueing downscaling for resolution: $resolution");
            EncodeDownscaleJob::dispatch($resolution, $mediaId);
        }

        Log::notice('Done scheduling Downscales');
    }

    private function canConvert(Media $media): bool
    {
        if (! $this->requirementsAreInstalled()) {
            return false;
        }

        return $this->canHandleMimeType(Str::lower($media->mime_type));
    }

    private function canHandleMimeType(string $mime): bool
    {
        return collect(MimeType::canHls())->contains($mime);
    }

    private function requirementsAreInstalled(): bool
    {
        return class_exists(FFProbe::class)
            && file_exists($this->ffProbe())
            && file_exists($this->ffMpeg());
    }
}
