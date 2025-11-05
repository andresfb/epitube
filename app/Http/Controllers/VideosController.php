<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use App\Actions\FeedAction;

class VideosController extends Controller
{
    public function __invoke(string $slug, FeedAction $feedAction): Factory|View
    {
        $feed = $feedAction->handle($slug);

        return view('video', ['video' => $feed]);
    }
}
