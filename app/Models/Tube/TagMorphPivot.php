<?php

declare(strict_types=1);

namespace App\Models\Tube;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

final class TagMorphPivot extends MorphPivot
{
    public $timestamps = false;

    protected static function boot(): void
    {
        parent::boot();

        self::created(static function (TagMorphPivot $pivot) {
            $pivot->pivotParent->touch();
        });
    }
}
