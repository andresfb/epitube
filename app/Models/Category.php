<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
    ];

    public static function getMain(): self
    {
        return self::where('main', true)->firstOrFail();
    }

    public static function getId(string $slug): int
    {
        return self::where('slug', $slug)->firstOrFail()->id;
    }

    public function contents(): HasMany|self
    {
        return $this->hasMany(Content::class);
    }
}
