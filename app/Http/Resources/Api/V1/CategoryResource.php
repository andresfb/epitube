<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Tube\Category;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

final class CategoryResource extends JsonApiResource
{
    public static string $model = Category::class;

    public array $attributes = [
        'id',
        'slug',
        'name',
        'icon',
        'main',
    ];
}
