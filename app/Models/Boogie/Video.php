<?php

namespace App\Models\Boogie;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property string $hash
 * @property string $title
 * @property string|null $url
 * @property string|null $thumbnail
 * @property string|null $duration
 * @property int $duration_numb
 * @property string|null $embedded
 * @property string|null $raw_tags
 * @property string $tags_hash
 * @property bool $active
 * @property bool $used
 * @property CarbonInterface|null $deleted_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class Video extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = Config::string('database.boogie');
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'duration_numb' => 'integer',
            'active' => 'boolean',
            'used' => 'boolean',
        ];
    }
}
