<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use App\Actions\Frontend\FeedGetDurationAction;
use App\Enums\Durations;

final class DurationController extends Controller
{
    public function __invoke(FeedGetDurationAction $action, Durations $duration): Factory|View
    {
        $feedList = $action->handle($duration, (int) request('page', 1));

        return view(
            'feed.duration',
            [
                'feed' => $feedList->feed,
                'links' => $feedList->links,
                'count' => $feedList->total,
                'duration' => Durations::title($duration),
                'range' => sprintf(
                    '(%s mins)',
                    collect(Durations::list($duration))
                        ->map(fn (int $seconds): int => (int) floor($seconds / 60))
                        ->implode(' to ')
                ),
            ]
        );
    }
}
