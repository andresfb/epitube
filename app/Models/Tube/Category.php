<?php

declare(strict_types=1);

namespace App\Models\Tube;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $icon
 * @property bool $main
 * @property CarbonInterface|null $deleted_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Content $content
 */
final class Category extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public static function getMain(): self
    {
        return self::where('main', true)->firstOrFail();
    }

    public static function getAlt(): self
    {
        return self::where('main', false)->firstOrFail();
    }

    public static function getId(string $slug): int
    {
        return (int) Cache::tags('categories')
            ->remember(
                md5(sprintf('%s:%s:%s', self::class, __FUNCTION__, $slug)),
                now()->addDay(),
                function () use ($slug): int {
                    return self::where('slug', $slug)->firstOrFail()->id;
                });
    }

    public static function getName(string $slug): string
    {
        return Cache::tags('categories')
            ->remember(
                md5(sprintf('%s:%s:%s', self::class, __FUNCTION__, $slug)),
                now()->addDay(),
                function () use ($slug): string {
                    return self::where('slug', $slug)->firstOrFail()->name;
                });
    }

    public static function getIcon(string $slug): string
    {
        return Cache::tags('categories')
            ->remember(
                md5(sprintf('%s:%s:%s', self::class, __FUNCTION__, $slug)),
                now()->addDay(),
                function () use ($slug): string {
                    return self::where('slug', $slug)->firstOrFail()->icon;
                });
    }

    public static function getSlugs(): array
    {
        return Cache::tags('categories')
            ->remember(
                md5(self::class.__FUNCTION__),
                now()->addDay(),
                function (): array {
                    return self::all()->pluck('slug')->toArray();
                });
    }

    public static function getRouterList(): array
    {
        return Cache::tags('categories')
            ->remember(
                md5(self::class.__FUNCTION__),
                now()->addDay(),
                function (): array {
                    $main = self::getMain();
                    $alt = self::getAlt();

                    return [[
                        'name' => $main->name,
                        'slug' => $main->slug,
                        'icon' => $main->icon,
                    ], [
                        'name' => $alt->name,
                        'slug' => $alt->slug,
                        'icon' => $alt->icon,
                    ]];
                });
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    protected function casts(): array
    {
        return [
            'main' => 'boolean',
        ];
    }
}
