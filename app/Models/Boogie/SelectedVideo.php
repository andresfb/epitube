<?php

declare(strict_types=1);

namespace App\Models\Boogie;

use App\Interfaces\DownloadableVideoInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
final class SelectedVideo extends Model implements DownloadableVideoInterface
{
    use SoftDeletes;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = Config::string('database.boogie');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'hash', 'hash');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function disable(): void
    {
        $this->update([
            'active' => false,
        ]);
    }

    public function markUsed(): void
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

    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('active', true)
            ->where('used', false)
            ->whereBetween('duration_numb', [300, 1200])
            ->orderBy('id');
    }
}
