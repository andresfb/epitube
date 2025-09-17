<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\MediaNamesLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    protected $with = ['category', 'tags', 'media'];

    public static function foundFileHash(string $hash): bool
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

    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name');

        $this->addMediaCollection(MediaNamesLibrary::videos())
            ->acceptsMimeTypes(MimeType::list())
            ->singleFile()
            ->useDisk($disk);

        $this->addMediaCollection(MediaNamesLibrary::transcoded())
            ->acceptsMimeTypes(['video/mp4'])
            ->singleFile()
            ->useDisk($disk);

        $this->addMediaCollection(MediaNamesLibrary::previews())
            ->acceptsMimeTypes([
                'video/mp4',
                'video/webm',
            ])
            ->useDisk($disk);

        $this->addMediaCollection('thumbnails')
            ->withResponsiveImages()
            ->acceptsMimeTypes([
                'image/png',
                'image/jpeg',
            ])
            ->useDisk($disk);
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
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

        return $content;
    }

    protected function casts(): array
    {
        return [
            'active' => 'bool',
            'viewed' => 'bool',
            'view_count' => 'int',
            'liked_count' => 'int',
            'added_at' => 'timestamp',
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
