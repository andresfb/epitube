<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use App\Actions\Frontend\FeedGetSelectsAction;
use App\Enums\Selects;

final class SelectController extends Controller
{
    public function __invoke(FeedGetSelectsAction $action, Selects $select): Factory|View
    {
        $feedList = $action->handle($select, (int) request('page', 1));

        return view(
            'feed.select',
            [
                'feed' => $feedList->feed,
                'links' => $feedList->links,
                'count' => $feedList->total,
                'select' => Selects::title($select),
            ]
        );
    }
}
