<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TagListResource;
use App\Models\Tube\Tag;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TagListController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        return TagListResource::collection(
            Tag::getWithCount(),
        );
    }
}
