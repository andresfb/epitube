<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ContentResource;
use App\Models\Tube\Content;
use App\Traits\ContentUpdatable;

final class ContentController extends Controller
{
    use ContentUpdatable;

    public function edit(string $slug): ContentResource
    {
        return ContentResource::make(
            Content::query()
                ->with('tags')
                ->where('slug', $slug)
                ->firstOrFail()
        );
    }
}
