<?php

namespace App\Models\Tube;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $hash
 * @property string $name
 * @property boolean $active
 * @property CarbonInterface|null $deleted_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class SharedTag extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $with = ['items'];

    public function items(): HasMany
    {
        return $this->hasMany(SharedTagItem::class)
            ->where('active', true);
    }

    public static function getList(): array
    {
        return Cache::tags('shared_tags')
            ->remember(
                md5('shared_tags'),
                now()->addDays(7),
                function (): array {
                    $tags = [];

                    self::query()
                        ->where('active', true)
                        ->get()
                        ->each(function (SharedTag $parent) use (&$tags) {
                            $tags[$parent->hash] = $parent->items->pluck('tag')->toArray();
                        });

                    return $tags;
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
