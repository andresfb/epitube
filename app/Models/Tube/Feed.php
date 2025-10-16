<?php

declare(strict_types=1);

namespace App\Models\Tube;

use App\Dtos\Tube\ContentItem;
use Carbon\CarbonInterface;
use Laravel\Scout\Searchable;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

/**
 * @property int $content_id
 * @property int $category_id
 * @property string $category
 * @property string $title
 * @property bool $active
 * @property bool $viewed
 * @property bool $liked
 * @property bool $published
 * @property int $order
 * @property int $view_count
 * @property string $service_url
 * @property array $tags
 * @property array $tag_slugs
 * @property array $videos
 * @property array $previews
 * @property array $thumbnails
 * @property array $related
 * @property CarbonInterface $added_at
 */
final class Feed extends Model
{
    use Searchable;

    protected $connection = 'mongodb';

    protected $guarded = [];

    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (Feed $model) {
            $model->order = 0;
            $model->published = false;
        });
    }

    public static function generate(Content $content): void
    {
        self::query()->updateOrCreate(
            ['content_id' => $content->id],
            ContentItem::withRelated($content)->toArray(),
        );
    }

    public static function activateFeed(int $contentId, int $index): void
    {
        if (! self::query()->where('content_id', $contentId)->exists()) {
            return;
        }

        self::query()
            ->where('content_id', $contentId)
            ->update([
                'order' => $index,
                'published' => true,
            ]);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
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
        ]);
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'viewed' => 'boolean',
            'liked' => 'boolean',
            'published' => 'boolean',
            'view_count' => 'integer',
            'order' => 'integer',
            'tags' => 'array',
            'tag_slugs' => 'array',
            'videos' => 'array',
            'previews' => 'array',
            'thumbnails' => 'array',
            'related' => 'array',
            'added_at' => 'datetime',
        ];
    }
}
