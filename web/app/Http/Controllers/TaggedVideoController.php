<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\FeedGetTaggedAction;
use App\Models\Tube\Tag;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

final class TaggedVideoController extends Controller
{
    public function __invoke(FeedGetTaggedAction $action, string $slug): Factory|View
    {
        $feedList = $action->handle($slug, (int) request('page', 1));

        return view(
            'tags.videos',
            [
                'feed' => $feedList->feed,
                'links' => $feedList->links,
                'count' => $feedList->total,
                'tag' => Tag::findFromStringOfAnyType($slug)->firstOrFail(),
            ]
        );
    }
}
