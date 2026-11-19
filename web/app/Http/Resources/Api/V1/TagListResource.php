<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

final class TagListResource extends JsonApiResource
{
    public array $attributes = [
        'slug',
        'name',
        'count',
    ];
}
