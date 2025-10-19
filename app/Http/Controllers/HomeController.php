<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FeedAction;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __invoke(FeedAction $feedAction): View
    {
        $feedItem = $feedAction->handle((int) request('page', 1));

        return view(
            'home',
            [
                'feed' => $feedItem->feed,
                'links' => $feedItem->links,
                'timeout' => Config::integer('feed.not_found_timeout', 5000),
                'maxRefresh' => Config::integer('feed.max_not_foud_runs', 3),
            ]
        );
    }
}
