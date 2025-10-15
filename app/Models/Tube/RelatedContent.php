<?php

declare(strict_types=1);

namespace App\Models\Tube;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RelatedContent extends Model
{
    protected $guarded = [];

    protected $with = ['related'];

    protected $touches = ['related', 'content'];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function related(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'related_content_id');
    }
}
