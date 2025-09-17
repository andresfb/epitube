<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * @property string $file_name
 */
final class Media extends BaseMedia
{
    use SoftDeletes;
}
