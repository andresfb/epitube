<?php

declare(strict_types=1);

namespace App\Models\Tube;

use App\Libraries\Tube\DiskNamesLibrary;
use App\Libraries\Tube\MediaNamesLibrary;
use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

#[ObservedBy([ContentObserver::class])]
final class Content extends Model implements HasMedia
{
    use HasTags;
    use InteractsWithMedia;
    use SoftDeletes;
    use Notifiable;

    protected $guarded = [];

    protected $with = ['category', 'tags', 'media', 'related'];

    public static function isDifferentFileVersion(string $hash, string $ogPath): bool
    {
        return self::where('file_hash', $hash)
            ->where('og_path', '!=', $ogPath)
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

    public function toFeedArray(): ?array
    {
        $content = $this->except([
            'item_id',
            'file_hash',
        ]);

        $content['id'] = $this->id;
        $content['category'] = $this->category->name;
        $content['viewed'] = $this->viewed ?? false;
        $content['liked'] = $this->liked ?? false;
        $content['view_count'] = $this->view_count ?? 0;

        $content['tags'] = $this->tags->pluck('name')->toArray();
        $content['tag_slugs'] = $this->tags->pluck('slug')->toArray();

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
            $content['duration'] = (int)$media->getCustomProperty('duration', 0);
            $content['height'] = sprintf('%sp', $media->getCustomProperty('height', 0));
        }

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
            get: static fn($value): int|float => $value / 1000,
            set: static fn($value): int|float => $value * 1000,
        );
    }
}
