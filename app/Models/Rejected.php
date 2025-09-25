<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rejected extends Model
{
    protected $table = 'rejected';

    protected $guarded = [];

    public static function getRejected(): array
    {
        return self::select('item_id')
            ->pluck('item_id')
            ->toArray();
    }
}
