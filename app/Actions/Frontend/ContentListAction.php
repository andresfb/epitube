<?php

declare(strict_types=1);

namespace App\Actions\Frontend;

use App\Dtos\Tube\ContentItem;
use App\Dtos\Tube\ContentListItem;
use App\Factories\ContentItemFactory;
use App\Models\Tube\Content;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final readonly class ContentListAction
{
    /**
     * @return Collection<ContentItem>
     *
     * @throws Exception
     */
    public function handle(ContentListItem $item): Collection
    {
        if ($item->isEmpty()) {
            $item->created_after = now()->startOfDay();
        }

        $key = md5(json_encode($item->toArray(), JSON_THROW_ON_ERROR));

        return Cache::tags('content')
            ->remember(
                $key,
                now()->addMinutes(5),
                static function () use ($item): Collection {
                    $request = Request::create('', 'GET', $item->toArray());

                    return QueryBuilder::for(Content::class, $request)
                        ->allowedFilters([
                            // Global search across title and content
                            AllowedFilter::callback('search', static function ($query, $value) {
                                $query->where(function ($query) use ($value) {
                                    $query->where('title', 'like', "%{$value}%")
                                        ->orWhereHas('category', function ($query) use ($value) {
                                            $query->where('name', 'like', "%{$value}%");
                                        });

                                    $tags = Content::makeTags($value);
                                    if ($tags->isNotEmpty()) {
                                        $query->orWhereHas('tags', function ($query) use ($tags) {
                                            $tagIds = collect($tags)->pluck('id');

                                            $query->whereIn(Content::getTagTablePrimaryKeyName(), $tagIds);
                                        });
                                    }
                                });
                            }),

                            // Exact filters for dropdowns
                            AllowedFilter::exact('category_id'),
                            AllowedFilter::exact('active'),
                            AllowedFilter::exact('viewed'),
                            AllowedFilter::exact('like_status'),

                            // Date range filters
                            AllowedFilter::callback('created_after', static function ($query, $value) {
                                $query->where('created_at', '>=', $value);
                            }),
                            AllowedFilter::callback('created_before', static function ($query, $value) {
                                $query->where('created_at', '<=', $value);
                            }),

                            AllowedFilter::callback('added_after', static function ($query, $value) {
                                $query->where('added_at', '>=', $value);
                            }),
                            AllowedFilter::callback('added_before', static function ($query, $value) {
                                $query->where('added_at', '<=', $value);
                            }),

                            // Custom scopes
                            AllowedFilter::scope('hasAllMedia'),
                        ])
                        ->allowedSorts([
                            'title',
                            'created_at',
                            'added_after',
                            'viewed',
                            'category_id',
                            'like_status',
                        ])
                        ->allowedIncludes(['tags'])
                        ->allowedIncludes(['category', 'tags', 'media'])
                        ->defaultSort('-created_at')
                        ->paginate(100)
                        ->appends($item->toArray())
                        ->map(function (Content $content): ContentItem {
                            return ContentItemFactory::forListing($content);
                        });
                });
    }
}
