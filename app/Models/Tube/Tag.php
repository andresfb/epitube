<?php

declare(strict_types=1);

namespace App\Models\Tube;

use App\Dtos\Tube\TagMenuItem;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag as SpatieTag;

final class Tag extends SpatieTag
{
    public static function getList(string $categorySlug): Collection
    {
        return Cache::tags('tags')
            ->remember(
                md5("TAG:LIST:$categorySlug"),
                now()->addHours(5),
                static function () use ($categorySlug): Collection {
                    return self::query()
                        ->select('tags.name', 'tags.slug', DB::raw('COUNT(taggables.tag_id) as count'))
                        ->contentFromCategory($categorySlug)
                        ->whereNull('tags.type')
                        ->groupBy('tags.id')
                        ->orderByDesc('count')
                        ->get()
                        ->map(function (Tag $tag): TagMenuItem {
                            return TagMenuItem::from($tag);
                        });
                }
            );
    }

    public static function getMenuList(string $categorySlug): Collection
    {
        return self::getList($categorySlug)
            ->take(Config::integer('constants.main_tags_limit') - 1);
    }

    #[Scope]
    protected function contentFromCategory(Builder $query, string $categorySlug): Builder
    {
        return $query->join('taggables', function (JoinClause $join) {
            return $join->on('tags.id', '=', 'taggables.tag_id')
                ->where('taggables.taggable_type', Content::class);
        })
            ->join('contents', 'taggables.taggable_id', '=', 'contents.id')
            ->join('categories', 'contents.category_id', '=', 'categories.id')
            ->where('categories.slug', $categorySlug);
    }
}
