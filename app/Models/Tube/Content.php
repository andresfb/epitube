<?php

declare(strict_types=1);

namespace App\Models\Tube;

use App\Dtos\Tube\ContentRelatedItem;
use App\Libraries\Tube\DiskNamesLibrary;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Observers\ContentObserver;
use App\Traits\ContentIdGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

/**
 * @property int $id
 * @property int $category_id
 * @property string $item_id
 * @property string $file_hash
 * @property string $slug
 * @property string $title
 * @property bool $active
 * @property bool $viewed
 * @property int $like_status
 * @property int $view_count
 * @property string $og_path
 * @property string $notes
 * @property CarbonImmutable $added_at
 * @property CarbonImmutable $deleted_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read Category $category
 * @property-read Collection<int, View> $views
 * @property-read Collection<int, Media> $media
 * @property-read Collection<int, Tag> $tags
 * @property-read Collection<int, Content> $related
 * @property-read Collection<int, Content> $relatedToThis
 */
#[ObservedBy([ContentObserver::class])]
final class Content extends Model implements HasMedia
{
    use ContentIdGenerator;
    use HasTags;
    use InteractsWithMedia;
    use Notifiable;
    use SoftDeletes;

    protected $guarded = [];

    protected $with = ['category', 'tags', 'media'];

    public static function isDifferentFileVersion(string $hash, string $ogPath): bool
    {
        return self::where('file_hash', $hash)
            ->where('og_path', '!=', $ogPath)
            ->exists();
    }

    public static function makeTags($values, $type = null, $locale = null): Collection
    {
        return self::convertToTags($values, $type, $locale);
    }

    public static function getImported(): array
    {
        return self::select('item_id')
            ->pluck('item_id')
            ->toArray();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'content_related',
            'content_id',
            'related_content_id'
        );
    }

    // Contents that list this content as related
    public function relatedToThis(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'content_related',
            'related_content_id',
            'content_id'
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaNamesLibrary::videos())
            ->singleFile()
            ->acceptsMimeTypes(MimeType::list())
            ->useDisk(DiskNamesLibrary::content());

        $this->addMediaCollection(MediaNamesLibrary::transcoded())
            ->singleFile()
            ->acceptsMimeTypes(['video/mp4'])
            ->useDisk(DiskNamesLibrary::media());

        $this->addMediaCollection(MediaNamesLibrary::downscaled())
            ->acceptsMimeTypes(['video/mp4'])
            ->useDisk(DiskNamesLibrary::media());

        $this->addMediaCollection(MediaNamesLibrary::previews())
            ->acceptsMimeTypes([
                'video/mp4',
                'video/webm',
            ])
            ->useDisk(DiskNamesLibrary::media());

        $this->addMediaCollection(MediaNamesLibrary::thumbnails())
            ->withResponsiveImages()
            ->acceptsMimeTypes([
                'image/png',
                'image/jpeg',
            ])
            ->useDisk(DiskNamesLibrary::media());
    }

    public function toFeedArray(): array
    {
        $content = $this->except([
            'item_id',
            'file_hash',
        ]);

        $content['id'] = $this->id;
        $content['category'] = $this->category->name;
        $content['viewed'] = $this->viewed ?? false;
        $content['view_count'] = $this->view_count ?? 0;

        $content['tags'] = $this->tags->pluck('name')->toArray();
        $content['tag_slugs'] = $this->tags->pluck('slug')->toArray();
        $content['tag_array'] = $this->tags->pluck('name', 'slug')->toArray();

        $content['service_url'] = sprintf(
            Config::string('jellyfin.item_web_url'),
            $this->item_id,
        );

        $collection = MediaNamesLibrary::videos();
        if ($this->hasMedia(MediaNamesLibrary::transcoded())) {
            $collection = MediaNamesLibrary::transcoded();
        }

        $media = $this->getMedia($collection)->first();
        if ($media !== null) {
            $content['duration'] = (int) $media->getCustomProperty('duration', 0);
            $content['height'] = sprintf('%sp', $media->getCustomProperty('height', 0));
        }

        return $content;
    }

    public function getRelatedIds(): array
    {
        $ids[] = $this->id;
        $maxCount = Config::integer('content.max_related_videos');

        // Load all related contents
        $idList = $this->related
            ->map(function (Content $item) use (&$ids): ContentRelatedItem {
                $ids[] = $item->id;

                return new ContentRelatedItem(
                    id: $item->id
                );
            })
            ->take($maxCount)
            ->toBase();

        if ($idList->count() >= $maxCount) {
            return $idList->toArray();
        }

        // If there aren't enough related items, load contents sharing the same tags
        $limit = $maxCount - $idList->count();
        $tags = $this->tags->pluck('name')->toArray();
        $tagged = self::query()
            ->withAnyTags($tags)
            ->whereNotIn('id', $ids)
            ->usable()
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function (Content $item) use (&$ids): ContentRelatedItem {
                $ids[] = $item->id;

                return new ContentRelatedItem(
                    id: $item->id
                );
            })
            ->toBase();

        $idList = $idList->merge($tagged);
        if ($idList->count() >= $maxCount) {
            return $idList->toArray();
        }

        // If there are still not enough items, fill the rest with a random set
        $limit = $maxCount - $idList->count();
        $contents = self::query()
            ->usable()
            ->whereNotIn('id', $ids)
            ->where('category_id', $this->category_id)
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function (Content $item): ContentRelatedItem {
                return new ContentRelatedItem(
                    id: $item->id
                );
            })
            ->toBase();

        return $idList->merge($contents)->toArray();
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (Content $content) {
            $content->slug = self::generateUniqueId();
        });
    }

    #[Scope]
    protected function hasVideos(Builder $query): Builder
    {
        return $query->whereHas('media', function (Builder $query): void {
            $query->where('collection_name', MediaNamesLibrary::transcoded())
                ->orWhere('collection_name', MediaNamesLibrary::videos());
        });
    }

    #[Scope]
    protected function hasThumbnails(Builder $query): Builder
    {
        return $query->whereHas('media', function (Builder $query): void {
            $query->where('collection_name', MediaNamesLibrary::thumbnails());
        });
    }

    #[Scope]
    protected function inMainCategory(Builder $query): Builder
    {
        return $query->where('category_id', Category::getMain()->id);
    }

    #[Scope]
    protected function inAltCategory(Builder $query): Builder
    {
        return $query->where('category_id', Category::getAlt()->id);
    }

    #[Scope]
    protected function hasAllMedia(Builder $query): Builder
    {
        return $query->hasVideos()
            ->hasThumbnails();
    }

    #[Scope]
    protected function usable(Builder $query): Builder
    {
        return $query->hasAllMedia()
            ->where('active', true)
            ->where('like_status', '>=', 0);
    }

    protected function casts(): array
    {
        return [
            'active' => 'bool',
            'viewed' => 'bool',
            'like_status' => 'int',
            'view_count' => 'int',
            'added_at' => 'datetime',
        ];
    }

    protected function viewCount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value): int|float => $value / 1000,
            set: static fn ($value): int|float => $value * 1000,
        );
    }
}
