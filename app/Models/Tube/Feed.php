<?php
/** @noinspection SelfClassReferencingInspection */

declare(strict_types=1);

namespace App\Models\Tube;

use App\Dtos\Tube\ContentItem;
use DateTime;
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
 * @property int $view_count
 * @property string $service_url
 * @property array $tags
 * @property array $tag_slugs
 * @property array $videos
 * @property array $previews
 * @property array $thumbnails
 * @property array $related
 * @property DateTime $expires_at
 * @property DateTime $added_at
 */
final class Feed extends Model
{
    use Searchable;

    protected $connection = 'mongodb';

    protected $guarded = [];

    public static function updateIfExists(Content $content): void
    {
        if (! Feed::where('content_id', $content->id)->exists()) {
            return;
        }

        self::generate($content);
    }

    public static function generate(Content $content): void
    {
        Feed::updateOrCreate(
            ['content_id' => $content->id],
            ContentItem::withRelated($content)->toArray(),
        );
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
        return $this->toArray();
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'viewed' => 'boolean',
            'liked' => 'boolean',
            'view_count' => 'integer',
            'tags' => 'array',
            'tag_slugs' => 'array',
            'videos' => 'array',
            'previews' => 'array',
            'thumbnails' => 'array',
            'related' => 'array',
            'expires_at' => 'datetime',
            'added_at' => 'datetime',
        ];
    }
}
