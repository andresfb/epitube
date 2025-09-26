<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\DiskNamesLibrary;
use App\Libraries\MediaNamesLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

final class Content extends Model implements HasMedia
{
    use HasTags;
    use InteractsWithMedia;
    use Searchable;
    use SoftDeletes;

    protected $guarded = [];

    protected $with = ['category', 'tags', 'media', 'related'];

    public static function fileHashExists(string $hash): bool
    {
        return self::where('file_hash', $hash)
            ->exists();
    }

    public static function getImported(): array
    {
        return self::select('item_id')
            ->pluck('item_id')
            ->toArray();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    public function related(): HasMany
    {
        return $this->hasMany(RelatedContent::class)
            ->limit(16);
    }

    public function scopeHasVideos(Builder $query): Builder
    {
        return $query->whereHas('media', function (Builder $query): void {
            $query->where('collection_name', MediaNamesLibrary::transcoded())
                ->orWhere('collection_name', MediaNamesLibrary::videos());
        });
    }

    public function scopeHasThumbnails(Builder $query): Builder
    {
        return $query->whereHas('media', function (Builder $query): void {
            $query->where('collection_name', MediaNamesLibrary::thumbnails());
        });
    }

    public function scopeInMainCategory(Builder $query): Builder
    {
        return $query->where('category_id', Category::getMain()->id);
    }

    public function scopeInAltCategory(Builder $query): Builder
    {
        return $query->where('category_id', Category::getAlt()->id);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaNamesLibrary::videos())
            ->acceptsMimeTypes(MimeType::list())
            ->useDisk(DiskNamesLibrary::content());

        $this->addMediaCollection(MediaNamesLibrary::transcoded())
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

        $this->addMediaCollection('thumbnails')
            ->withResponsiveImages()
            ->acceptsMimeTypes([
                'image/png',
                'image/jpeg',
            ])
            ->useDisk(DiskNamesLibrary::media());
    }

    public function searchableAs(): string
    {
        return 'epitube_content_index';
    }

    public function toSearchableArray(): ?array
    {
        $content = $this->except([
            'item_id',
            'file_hash',
        ]);

        $content['category'] = $this->category->name;
        $content['tags'] = $this->tags->pluck('name')->toArray();
        $content['service_url'] = sprintf(
            Config::string('jellyfin.item_web_url'),
            $this->item_id,
        );

        return $content;
    }

    protected function casts(): array
    {
        return [
            'active' => 'bool',
            'viewed' => 'bool',
            'liked' => 'bool',
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
