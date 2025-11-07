<?php

declare(strict_types=1);

namespace App\Models\Tube;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $content_id
 * @property int $seconds_played
 */
final class View extends Model
{
    protected $guarded = [];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    protected function secondsPlayed(): Attribute
    {
        return Attribute::make(
            get: static fn ($value): int|float => $value / 1000,
            set: static fn ($value): int|float => $value * 1000,
        );
    }
}
