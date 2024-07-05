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
        'name_hash',
        'file_hash',
        'title',
        'active',
        'og_path',
        'og_file',
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

        $this->addMediaCollection('transcoded')
            ->acceptsMimeTypes(['video/mp4'])
            ->singleFile()
            ->useDisk('media');

        $this->addMediaConversion('thumb')
            ->format('jpg')
            ->withResponsiveImages()
            ->extractVideoFrameAtSecond(20)
            ->performOnCollections('videos', 'transcoded');
    }

    public static function found(string $hash): bool
    {
        return self::where('name_hash', $hash)
            ->orWhere('file_hash', $hash)
            ->exists();
    }
}
