<?php

namespace App\Models\Tube;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class TagMorphPivot extends MorphPivot
{
    public $timestamps = false;

    protected static function boot(): void
    {
        parent::boot();

        static::created(static function (TagMorphPivot $pivot) {
            $pivot->pivotParent->touch();
        });
    }
}
