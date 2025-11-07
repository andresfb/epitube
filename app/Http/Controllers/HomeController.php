<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\FeedsAction;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __invoke(FeedsAction $feedAction): View
    {
        $feedList = $feedAction->handle((int) request('page', 1));

        return view(
            'home',
            [
                'feed' => $feedList->feed,
                'links' => $feedList->links,
                'showTags' => true,
                'timeout' => Config::integer('feed.not_found_timeout', 5000),
                'maxRefresh' => Config::integer('feed.max_not_foud_runs', 3),
            ]
        );
    }
}
