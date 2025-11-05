<?php

declare(strict_types=1);

namespace App\Models\Tube;

use App\Dtos\Tube\ImportVideoItem;
use Illuminate\Database\Eloquent\Model;

final class Rejected extends Model
{
    protected $table = 'rejected';

    protected $guarded = [];

    public static function getRejected(): array
    {
        return self::select('item_id')
            ->pluck('item_id')
            ->toArray();
    }

    public static function reject(ImportVideoItem $videoItem, string $message): void
    {
        self::updateOrCreate([
            'item_id' => $videoItem->Id,
        ], [
            'og_path' => $videoItem->Path,
            'reason' => $message,
            'duration' => $videoItem->Duration,
            'height' => $videoItem->Height,
            'width' => $videoItem->Width,
        ]);
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'duration' => 'integer',
            'height' => 'integer',
            'width' => 'integer',
        ];
    }
}
