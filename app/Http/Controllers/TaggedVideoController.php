<?php

namespace App\Http\Controllers;

use App\Actions\Frontend\FeedGetTaggedAction;
use App\Models\Tube\Tag;

class TaggedVideoController extends Controller
{
    public function __invoke(FeedGetTaggedAction $action, string $slug)
    {
        $feedList = $action->handle($slug, (int) request('page', 1));

        return view(
            'tags.videos',
            [
                'feed' => $feedList->feed,
                'links' => $feedList->links,
                'count' => $feedList->total,
                'tag' => Tag::findFromStringOfAnyType($slug)->firstOrFail()
            ]
        );
    }
}
