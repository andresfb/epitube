<?php

declare(strict_types=1);

namespace App\Models\Tube;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class View extends Model
{
    protected $fillable = [
        'content_id',
        'time_code',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
