<?php

namespace App\Models\Tube;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class SearchableWord extends Model
{
//    use Searchable;

    protected $guarded = [];

    public $timestamps = false;

    public function searchableAs(): string
    {
        return 'epitube_words_index';
    }

    public function toSearchableArray(): array
    {
        return $this->toArray();
    }
}
