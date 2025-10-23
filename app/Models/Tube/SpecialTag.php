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
 * @property string|null $value
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

    public static function getBanded(): array
    {
        return Cache::tags('special-tags')
            ->remember(
                md5('special-tags-banded'.SpecialTagType::BANDED->value),
                now()->addDays(7),
                function (): array {
                    return self::query()
                        ->where('type', SpecialTagType::BANDED)
                        ->where('active', true)
                        ->get()
                        ->pluck('tag')
                        ->toArray();
                });
    }

    public static function getDeTitle(): array
    {
        return Cache::tags('special-tags')
            ->remember(
                md5('special-tags-banded'.SpecialTagType::DE_TITLE_WORDS->value),
                now()->addDays(7),
                function (): array {
                    return self::query()
                        ->where('type', SpecialTagType::DE_TITLE_WORDS)
                        ->where('active', true)
                        ->get()
                        ->pluck('tag')
                        ->toArray();
                });
    }

    /**
     * @return array<SpecialTag>
     */
    public static function getReTitle(): array
    {
        return Cache::tags('special-tags')
            ->remember(
                md5('special-tags-banded'.SpecialTagType::RE_TITLE_WORDS->value),
                now()->addDays(7),
                function (): array {
                    return self::query()
                        ->where('type', SpecialTagType::RE_TITLE_WORDS)
                        ->where('active', true)
                        ->get()
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
