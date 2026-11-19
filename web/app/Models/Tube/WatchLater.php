<?php

declare(strict_types=1);

namespace App\Models\Tube;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $content_id
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read Content $content
 */
final class WatchLater extends Model
{
    protected $guarded = [];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
