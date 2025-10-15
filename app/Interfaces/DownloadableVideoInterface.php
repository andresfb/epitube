<?php

namespace App\Interfaces;

interface DownloadableVideoInterface
{
    public function getId(): int;

    public function getUrl(): string;

    public function getHash(): string;

    public function disable(): void;

    public function markUsed(): void;

}
