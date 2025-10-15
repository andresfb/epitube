<?php

namespace App\Models\Tube;

use App\Dtos\Tube\TagMenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag as SpatieTag;

class Tag extends SpatieTag
{
    public static function getList(): Collection
    {
        return Cache::tags('tags')
            ->remember(
                md5('TAG:LIST'),
                now()->addHours(5),
                static function (): Collection {
                    return self::query()
                        ->select('tags.name', 'tags.slug', DB::raw('COUNT(taggables.tag_id) as count'))
                        ->join('taggables', 'tags.id', '=', 'taggables.tag_id')
                        ->where('taggables.taggable_type', Content::class)
                        ->where('tags.type', 'main')
                        ->groupBy('tags.id')
                        ->orderByDesc('count')
                        ->get()
                        ->map(function (Tag $tag) {
                            return TagMenuItem::from($tag);
                        });
                }
            );
    }

    public static function getMainList(): Collection
    {
        return self::getList()
            ->sortBy('name')
            ->take(Config::integer('constants.main_tags_limit') - 1);
    }
}
