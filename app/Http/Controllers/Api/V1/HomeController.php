<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FeedListResource;
use App\Services\Tube\Feed\FeedService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HomeController extends Controller
{
    public function __invoke(FeedService $service): AnonymousResourceCollection
    {
        return FeedListResource::collection(
            $service->getFeed(
                page: (int) request('page', 1),
                fromRequest: true,
            )
        );
    }
}
