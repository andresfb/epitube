<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Selects;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FeedListResource;
use App\Services\Tube\Feed\FeedSelectsService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SelectController extends Controller
{
    public function __invoke(
        FeedSelectsService $service,
        Selects $select): AnonymousResourceCollection
    {
        return FeedListResource::collection(
            $service->execute(
                select: $select,
                page: (int) request('page', 1),
            )
        )
        ->additional([
            'meta' => [
                'select' => Selects::title($select),
            ],
        ]);
    }
}
