<?php

declare(strict_types=1);

namespace App\Models\Tube;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Searchable;

final class SearchableWord extends Model
{
    use Searchable;

    public $timestamps = false;

    protected $guarded = [];

    public function searchableAs(): string
    {
        return Config::string('content.search_word_index');
    }

    public function toSearchableArray(): array
    {
        $word = $this->except('words');
        $word['word'] = $this->words;

        return $word;
    }
}
