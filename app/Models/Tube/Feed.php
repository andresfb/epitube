<?php

declare(strict_types=1);

namespace App\Models\Tube;

use App\Dtos\Tube\ContentItem;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Scout;
use Laravel\Scout\Searchable;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property int $id
 * @property int $category_id
 * @property string $category
 * @property string $slug
 * @property string $title
 * @property bool $active
 * @property string $duration,
 * @property int $length,
 * @property string $resolution,
 * @property bool $is_hd,
 * @property bool $viewed
 * @property int $like_status
 * @property bool $published
 * @property int $order
 * @property int $view_count
 * @property string $service_url
 * @property array $tags
 * @property array $tag_slugs
 * @property array $tag_array
 * @property array $videos
 * @property array $previews
 * @property array $thumbnails
 * @property array $related
 * @property CarbonInterface $added_at
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 */
final class Feed extends Model
{
    use Searchable;

    protected $connection = 'mongodb';

    protected $guarded = [];

    public static function generate(Content $content): void
    {
        self::query()->updateOrCreate(
            ['id' => $content->id],
            ContentItem::withRelated($content)->toArray()
        );
    }

    public static function activateFeed(Content $content, int $index = 1): void
    {
        if (self::query()->where('id', $content->id)->doesntExist()) {
            self::generate($content);
        }

        self::query()
            ->where('id', $content->id)
            ->update([
                'order' => $index,
                'published' => true,
            ]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function searchableAs(): string
    {
        return 'epitube_feed_index';
    }

    public function toSearchableArray(): ?array
    {
        return $this->except([
            'order',
            'published',
            'tag_array',
            'videos',
            'previews',
            'thumbnails',
            'service_url',
            'related',
        ]);
    }

    /**
     * @param  Collection  $models
     */
    public function queueMakeSearchable($models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $cacheKey = md5(sprintf(
            'FEED:MAKE:SEARCHABLE:%s',
            $models->pluck('id')->implode(',')
        ));

        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addSeconds(5));

        if (! config('scout.queue')) {
            $this->syncMakeSearchable($models);
        }

        dispatch((new Scout::$makeSearchableJob($models))
            ->onQueue($models->first()->syncWithSearchUsingQueue())
            ->onConnection($models->first()->syncWithSearchUsing()));
    }

    public function toArray(): array
    {
        $item = $this->toSearchableArray();
        $item['added_at'] = CarbonImmutable::parse($this->added_at->toDateTimeString());

        return $item;
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (Feed $model) {
            $model->order = 0;
            $model->published = false;
        });
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'viewed' => 'boolean',
            'like_status' => 'int',
            'published' => 'boolean',
            'is_hd' => 'boolean',
            'view_count' => 'integer',
            'order' => 'integer',
            'tags' => 'array',
            'tag_slugs' => 'array',
            'tag_array' => 'array',
            'videos' => 'array',
            'previews' => 'array',
            'thumbnails' => 'array',
            'related' => 'array',
            'added_at' => 'datetime',
        ];
    }
}
