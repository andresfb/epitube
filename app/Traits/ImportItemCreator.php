<?php

namespace App\Traits;

use App\Dtos\ImportVideoItem;

trait ImportItemCreator
{
    private function createItem(array $apiItem): ImportVideoItem
    {
        $fileInfo = pathinfo((string) $apiItem['Path']);

        return new ImportVideoItem(
            Id: $apiItem['Id'],
            Name: $fileInfo['filename'],
            Path: $apiItem['Path'],
            MimeType: mime_content_type($apiItem['Path']),
            RunTimeTicks: (int) ($apiItem['RunTimeTicks'] ?? 0),
            Width: (int) ($apiItem['Width'] ?? 0),
            Height: (int) ($apiItem['Height'] ?? 0),
        );
    }
}
