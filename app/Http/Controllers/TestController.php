<?php

namespace App\Http\Controllers;

use App\Dtos\Tube\FeedItem;
use App\Models\Tube\Content;
use App\Models\Tube\Feed;

class TestController extends Controller
{
    public function __invoke()
    {
        // TODO: implement Tagify: https://github.com/yairEO/tagify

        $content = Content::query()
            ->where('active', true)
            ->inRandomOrder()
            ->firstOrFail();

        $feed = Feed::query()
            ->where('slug', $content->slug)
            ->firstOrFail();

        $feedItem = FeedItem::forDetail($feed);

        return view('test.index', ['feed' => $feedItem]);
    }
}
