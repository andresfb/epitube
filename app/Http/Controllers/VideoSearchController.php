<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\VideoSearchAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class VideoSearchController extends Controller
{
    public function __invoke(Request $request, VideoSearchAction $action): View|RedirectResponse
    {
        $request->validate([
            'term' => 'string|required|min:2',
        ]);

        $feed = $action->handle($request->term);

        if ($feed->total === 0) {
            return redirect()->route('home')
                ->with('error', 'No videos found for your search.');
        }

        return view(
            'home',
            [
                'feed' => $feed->feed,
                'links' => $feed->links,
                'count' => $feed->total,
                'title' => "Search Results for: $request->term",
                'term' => $request->term,
            ]
        );
    }
}
