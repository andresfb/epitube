<?php

namespace App\Http\Controllers\Api\V1;

use App\Dtos\Tube\VideoSearchItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\VideoSearchRequest;
use App\Http\Resources\Api\V1\FeedListResource;
use App\Services\Tube\Feed\VideoSearchService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VideoSearchController extends Controller
{
    public function __invoke(
        VideoSearchRequest $request,
        VideoSearchService $service): AnonymousResourceCollection
    {
        return FeedListResource::collection(
            $service->execute(
                VideoSearchItem::from($request)
            ),
        )
        ->additional([
            'meta' => [
                'title' => "Search Results for: $request->term",
                'term' => $request->term,
            ],
        ]);
    }
}
