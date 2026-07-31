<?php

declare(strict_types=1);

namespace App\Contracts;

interface FeedMirror
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateBySlug(string $slug, array $attributes): void;
}
