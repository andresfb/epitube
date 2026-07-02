<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Durations;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FeedListResource;
use App\Services\Tube\Feed\FeedDurationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DurationController extends Controller
{
    public function __invoke(
        FeedDurationService $service,
        Durations $duration): AnonymousResourceCollection
    {
        return FeedListResource::collection(
            $service->execute(
                duration: $duration,
                page: (int) request('page', 1),
            )
        )
        ->additional([
            'meta' => [
                'duration' => Durations::title($duration),
                'range' => sprintf(
                    '(%s mins)',
                    collect(Durations::list($duration))
                        ->map(fn (int $seconds): int => (int) floor($seconds / 60))
                        ->implode(' to ')
                ),
            ],
        ]);
    }
}
