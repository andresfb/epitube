<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Tube\Tag;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @mixin Tag */
class TagResource extends JsonApiResource
{
    public static string $model = Tag::class;

    public array $attributes = [
        'name',
        'slug',
        'order_column',
    ];
}
