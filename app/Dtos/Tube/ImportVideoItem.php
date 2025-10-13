<?php

declare(strict_types=1);

namespace App\Dtos\Tube;

use Spatie\LaravelData\Data;

final class ImportVideoItem extends Data
{
    public function __construct(
        public string $Id,
        public string $Name,
        public string $Path,
        public string $MimeType,
        public int $RunTimeTicks = 0,
        public int $Width = 0,
        public int $Height = 0,
        public int $Duration = 0,
    ) {}

    public function withVideoInfo(VideoInfoItem $infoItem): self
    {
        $width = $this->Width;
        if ($infoItem->width !== $this->Width) {
            $width = $infoItem->width;
        }

        $height = $this->Height;
        if ($infoItem->height !== $this->Height) {
            $height = $infoItem->height;
        }

        $duration = $this->Duration;
        if ($infoItem->duration !== $this->Duration) {
            $duration = $infoItem->duration;
        }

        return new self(
            $this->Id,
            $this->Name,
            $this->Path,
            $this->MimeType,
            $this->RunTimeTicks,
            $width,
            $height,
            $duration,
        );
    }
}
