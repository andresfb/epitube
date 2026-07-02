<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Tube\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            Category::query()
                ->orderByDesc('main')
                ->get()
        );
    }
}
