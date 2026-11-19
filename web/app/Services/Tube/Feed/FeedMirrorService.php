<?php

declare(strict_types=1);

namespace App\Services\Tube\Feed;

use App\Contracts\FeedMirror;
use App\Models\Tube\Feed;

final class FeedMirrorService implements FeedMirror
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateBySlug(string $slug, array $attributes): void
    {
        Feed::query()
            ->where('slug', $slug)
            ->update($attributes);
    }
}
