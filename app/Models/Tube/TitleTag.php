<?php

namespace App\Models\Tube;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $hash
 * @property string $word
 * @property string $tag
 * @property boolean $active
 * @property CarbonInterface|null $deleted_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class TitleTag extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public static function getList(): array
    {
        return Cache::tags('title-tags')
            ->remember(
                md5('title-tags-list'),
                now()->addDays(7),
                function (): array {
                    return TitleTag::query()
                        ->where('active', true)
                        ->get()
                        ->pluck('tag', 'word')
                        ->toArray();
                }
            );
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
