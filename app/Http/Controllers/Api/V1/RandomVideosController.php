<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Dtos\Tube\RandomVideoItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\RandomVideoRequest;
use App\Http\Resources\Api\V1\FeedListResource;
use App\Services\Tube\Feed\FeedService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class RandomVideosController extends Controller
{
    public function __invoke(RandomVideoRequest $form, FeedService $service): AnonymousResourceCollection
    {
        $filters = RandomVideoItem::from($form);

        return FeedListResource::collection(
            $service->randomVideos($filters)
        )
            ->additional([
                'meta' => [
                    'filters' => $filters,
                    'range' => [5, 25, 50, 75, 100],
                ],
            ]);
    }
}
