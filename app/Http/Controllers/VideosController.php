<?php

namespace App\Http\Controllers;

use App\Actions\FeedAction;

class VideosController extends Controller
{
    public function __invoke(string $slug, FeedAction $feedAction)
    {
        $feed = $feedAction->handle($slug);

        return view('video', ['video' => $feed]);
    }
}
