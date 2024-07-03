<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

class Content extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;
    use HasTags;

    protected $fillable = [
        'hash',
        'title',
        'active',
        'og_path',
        'og_file',
        'source',
        'source_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'bool',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('videos')
            ->acceptsMimeTypes(MimeType::list())
            ->singleFile()
            ->useDisk('media');

        $this->addMediaCollection('thumbnail')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/heic',
            ])->singleFile()
            ->useDisk('media');

        $this->addMediaConversion('thumb')
            ->format('jpg')
            ->width(600)
            ->sharpen(8)
            ->performOnCollections('thumbnail');
    }

    public static function found(string $hash): bool
    {
        return self::whereHash($hash)->exists();
    }
}
