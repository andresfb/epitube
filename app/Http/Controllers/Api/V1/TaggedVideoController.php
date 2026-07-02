<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FeedListResource;
use App\Models\Tube\Tag;
use App\Services\Tube\Feed\FeedService;

final class TaggedVideoController extends Controller
{
    public function __invoke(FeedService $service, string $slug)
    {
        $tag = Tag::findFromStringOfAnyType($slug)
            ->firstOrFail();

        return FeedListResource::collection(
            $service->videosByTag($slug, (int) request('page', 1))
        )
            ->additional([
                'meta' => [
                    'tag' => $tag->only('slug', 'name'),
                ],
            ]);
    }
}
