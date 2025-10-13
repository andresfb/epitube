<?php

namespace App\Dtos\Boogie;

use Carbon\CarbonInterface;

final class DownloadStatusItem
{
    public function __construct(
        public int $count,
        public int $runs,
        public CarbonInterface $started,
    ) {}

    public function incrementRuns(): self
    {
        return new self(
            $this->count,
            ++$this->runs,
            $this->started,
        );
    }

    public function increment(): self
    {
        return new self(
            ++$this->count,
            ++$this->runs,
            $this->started,
        );
    }
}
