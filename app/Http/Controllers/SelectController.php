<?php

namespace App\Http\Controllers;

use App\Actions\Frontend\FeedGetSelectsAction;
use App\Enums\Selects;

class SelectController extends Controller
{
    public function __invoke(FeedGetSelectsAction $action, Selects $select)
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
