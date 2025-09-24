<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\EncodeDownscaleJob;
use App\Libraries\MasterVideoLibrary;
use App\Models\Media;
use App\Models\MimeType;
use App\Traits\Encodable;
use Exception;
use FFMpeg\FFProbe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class GenerateDownscalesService
{
    use Encodable;

    public const array RESOLUTIONS = [
        '360p' => 360,
        '480p' => 480,
        '720p' => 720,
        '1080p' => 1080,
        '1440p' => 1440,
        '2160p' => 2160,
    ];

    public function __construct(private MasterVideoLibrary $videoLibrary) {}

    /**
     * @throws Exception
     */
    public function execute(int $mediaId): void
    {
        Log::notice("Starting generating downscales for: $mediaId");
        $media = Media::where('id', $mediaId)
            ->firstOrFail();

        if (! $this->canConvert($media)) {
            throw new RuntimeException("Media not supported: {$media->id}");
        }

        $this->videoLibrary->downloadMaster($media);

        $resolutions = collect(self::RESOLUTIONS)
            ->filter(fn (int $resolution): bool => $resolution < (int) $media->getCustomProperty('height'));

        foreach ($resolutions as $resolution) {
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
