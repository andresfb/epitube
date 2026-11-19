<?php

declare(strict_types=1);

namespace App\Services\Tube;

use App\Exceptions\ProcessRunningException;
use App\Libraries\Tube\MasterVideoLibrary;
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
        $this->videoLibrary->setMediaId($mediaId)
            ->prepare(static::class.$clasSuffix);

        $this->flag = sprintf('%s/creating', $this->videoLibrary->getTempPath());
        $this->checkFlag(
            disk: $this->videoLibrary->getProcessingDisk(),
            mediaId: $mediaId,
            mediaName: '',
        );

        $this->createFlag($this->videoLibrary->getProcessingDisk());
    }
}
