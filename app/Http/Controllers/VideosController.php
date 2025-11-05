<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FeedAction;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

final class VideosController extends Controller
{
    public function __invoke(string $slug, FeedAction $feedAction): Factory|View
    {
        $feed = $feedAction->handle($slug);

        return view('video', ['video' => $feed]);
    }
}
