<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Tube\Content;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @mixin Content */
final class ContentResource extends JsonApiResource
{
    public static string $model = Content::class;

    public array $attributes = [
        'id',
        'category_id',
        'item_id',
        'file_hash',
        'slug',
        'title',
        'active',
        'viewed',
        'like_status',
        'view_count',
        'featured',
        'og_path',
        'notes',
        'added_at',
        'created_at',
        'updated_at',
    ];

    public array $relationships = [
        'category' => CategoryResource::class,
        'tags' => TagResource::class,
    ];
}
