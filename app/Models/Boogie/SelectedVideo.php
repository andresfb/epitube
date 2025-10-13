<?php

namespace App\Models\Boogie;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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
class SelectedVideo extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = Config::string('database.boogie');
    }

    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('active', true)
            ->where('used', false)
            ->whereBetween('duration_numb', [300, 1200])
            ->orderBy('id');
    }

    public function disable(): void
    {
        $this->update([
            'active' => false,
        ]);
    }

    public function markedUsed(): void
    {
        $this->update([
            'used' => true,
        ]);
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
