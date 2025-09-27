<?php

namespace App\Models;

use App\Dtos\ImportVideoItem;
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
        ]);
    }
}
