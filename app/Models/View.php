<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class View extends Model
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
