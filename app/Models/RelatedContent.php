<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatedContent extends Model
{
    protected $guarded = [];

    protected $with = ['related'];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function related(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'related_content_id');
    }
}
