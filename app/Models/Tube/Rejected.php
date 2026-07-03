<?php

declare(strict_types=1);

namespace App\Models\Tube;

use App\Dtos\Tube\ImportVideoItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $item_id
 * @property string $og_path
 * @property string $reason
 * @property int $duration
 * @property int $height
 * @property int $width
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
final class Rejected extends Model
{
    protected $table = 'rejected';

    protected $guarded = [];

    public static function getRejected(): array
    {
        return self::query()
            ->select('item_id')
            ->pluck('item_id')
            ->toArray();
    }

    public static function reject(ImportVideoItem $videoItem, string $message): void
    {
        self::query()
            ->updateOrCreate([
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
