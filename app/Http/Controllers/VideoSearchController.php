<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\VideoSearchAction;
use App\Dtos\Tube\VideoSearchItem;
use App\Http\Requests\VideoSearchRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class VideoSearchController extends Controller
{
    public function __invoke(VideoSearchRequest $request, VideoSearchAction $action): View|RedirectResponse
    {
        $feed = $action->handle(
            VideoSearchItem::from($request),
        );

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
