<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FeedDetailResource;
use App\Services\Tube\Feed\FeedService;

final class VideoController extends Controller
{
    public function __invoke(FeedService $service, string $slug): FeedDetailResource
    {
        return FeedDetailResource::make(
            $service->getVideo($slug)
        );
    }
}
