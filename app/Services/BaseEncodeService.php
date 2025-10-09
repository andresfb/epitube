<?php

namespace App\Services;

use App\Exceptions\ProcessRunningException;
use App\Libraries\MasterVideoLibrary;
use App\Traits\Encodable;

abstract class BaseEncodeService
{
    use Encodable;

    public function __construct(protected readonly MasterVideoLibrary $videoLibrary) {}

    /**
     * @throws ProcessRunningException
     */
    protected function prepare(int $mediaId, string $clasSuffix = ''): void
    {
        $this->videoLibrary->prepare($mediaId, static::class.$clasSuffix);

        $this->flag = sprintf('%s/creating', $this->videoLibrary->getTempPath());
        $this->checkFlag(
            disk: $this->videoLibrary->getProcessingDisk(),
            mediaId: $mediaId,
            mediaName: '',
        );

        $this->createFlag($this->videoLibrary->getProcessingDisk());
    }
}
