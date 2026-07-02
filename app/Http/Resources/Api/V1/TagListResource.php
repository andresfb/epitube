<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class TagListResource extends JsonApiResource
{
    public array $attributes = [
        'slug',
        'name',
        'count',
    ];
}
