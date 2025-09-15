<?php

namespace App\Models;

use Aliziodev\LaravelTaxonomy\Traits\HasTaxonomy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Content extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;
    use HasTaxonomy;

    protected $guarded = [];

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

    public function registerMediaCollections(): void
    {
        $disk = config('media-library.disk_name');

        $this->addMediaCollection('videos')
            ->acceptsMimeTypes(MimeType::list())
            ->singleFile()
            ->useDisk($disk);

        $this->addMediaCollection('transcoded')
            ->acceptsMimeTypes(['video/mp4'])
            ->singleFile()
            ->useDisk($disk);

        $this->addMediaCollection('previews')
            ->useDisk($disk);

        $this->addMediaCollection('thumbnail')
            ->withResponsiveImages()
            ->acceptsMimeTypes([
                'image/png',
                'image/jpeg',
            ])
            ->singleFile()
            ->useDisk($disk);
    }

    public static function found(string $hash): bool
    {
        return self::where('name_hash', $hash)
            ->orWhere('file_hash', $hash)
            ->exists();
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    public static function getImported(): array
    {
        return self::select('name_hash')
            ->pluck('name_hash')
            ->toArray();
    }

    protected function viewCount(): Attribute
    {
        return Attribute::make(
            get: static fn($value) => $value / 1000,
            set: static fn($value) => $value * 1000,
        );
    }
}
