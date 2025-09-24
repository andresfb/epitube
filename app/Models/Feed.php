<?php

declare(strict_types=1);

namespace App\Models;

use App\Dtos\ContentItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use function Laravel\Prompts\select;

final class Feed extends Model
{
    protected $guarded = [];

    protected $with = ['category'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    protected function casts(): array
    {
        return [
            'content' => 'json',
            'expires_at' => 'datetime',
            'added_at' => 'datetime',
        ];
    }

    public static function updateIfExists(Content $content): void
    {
        if (! self::where('content_id', $content->id)->exists()) {
            return;
        }

        self::generate($content);
    }

    public static function generate(Content $content): void
    {
        self::updateOrCreate([
            'content_id' => $content->id,
        ], [
            'category_id' => $content->category_id,
            'content' => ContentItem::withRelated($content)->toArray(),
            'expires_at' => now()->addDay()->subSecond(),
            'added_at' => $content->added_at,
        ]);
    }
}
