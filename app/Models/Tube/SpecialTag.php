<?php

namespace App\Models\Tube;

use App\Enums\SpecialTagType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $slug
 * @property string $tag
 * @property SpecialTagType $type
 * @property bool $active
 * @property CarbonInterface|null $deleted_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class SpecialTag extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public static function getList(SpecialTagType $type): array
    {
        return Cache::tags('special-tags')
            ->remember(
                md5('special-tags-banded'.$type->value),
                now()->addDays(7),
                function () use ($type): array {
                    return self::query()
                        ->where('type', $type)
                        ->where('active', true)
                        ->get()
                        ->pluck('tag')
                        ->toArray();
                });
    }

    protected function casts(): array
    {
        return [
            'type' => SpecialTagType::class,
            'active' => 'boolean',
        ];
    }
}
