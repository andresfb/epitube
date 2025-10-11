<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

final class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
    ];

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
        return Cache::tags('categories')
            ->remember(
                md5(sprintf("%s:%s:%s", self::class, __FUNCTION__, $slug)),
                now()->addDay(),
                function () use ($slug): int {
                    return self::where('slug', $slug)->firstOrFail()->id;
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
//        return [];
        return Cache::tags('categories')
            ->remember(
                md5(self::class.__FUNCTION__),
                now()->addDay(),
                function (): array {
                    $main = self::getMain();
                    $alt = self::getAlt();

                    return [[
                        'name' => $main->name,
                        'route' => route(
                            'switch.category',
                            ['category' => $main->slug]
                        )
                    ], [
                        'name' => $alt->name,
                        'route' => route(
                            'switch.category',
                            ['category' => $alt->slug]
                        )
                    ]];
                });
    }

    public function contents(): HasMany|self
    {
        return $this->hasMany(Content::class);
    }
}
