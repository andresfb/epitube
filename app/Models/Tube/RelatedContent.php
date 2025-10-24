<?php

declare(strict_types=1);

namespace App\Models\Tube;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class RelatedContent extends Pivot
{
    protected $guarded = [];

//    public function content(): BelongsTo
//    {
//        return $this->belongsTo(Content::class);
//    }
//
//    public function related(): BelongsTo
//    {
//        return $this->belongsTo(Content::class, 'related_content_id');
//    }
}
