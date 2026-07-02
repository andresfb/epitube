<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FeedDetailResource;
use App\Services\Tube\Feed\FeedService;

class VideoController extends Controller
{
    public function __invoke(FeedService $service, string $slug): FeedDetailResource
    {
        return FeedDetailResource::make(
            $service->getVideo($slug)
        );
    }
}
