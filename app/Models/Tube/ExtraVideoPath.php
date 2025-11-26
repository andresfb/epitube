<?php

namespace App\Models\Tube;

use Illuminate\Database\Eloquent\Model;

final class ExtraVideoPath extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /**
     * @return array<string>
     */
    public static function getActive(): array
    {
        return self::query()
            ->where('active', true)
            ->get()
            ->pluck('path')
            ->toArray();
    }
}
