<?php

namespace App\Models;

use App\Models\Tube\SharedTag;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $shared_tag_id
 * @property string $hash
 * @property string $tag
 * @property boolean $active
 * @property CarbonInterface|null $deleted_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class SharedTagItem extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function shareTag(): BelongsTo
    {
        return $this->belongsTo(SharedTag::class);
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
