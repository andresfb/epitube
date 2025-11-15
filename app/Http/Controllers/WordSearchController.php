<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\WordSearchAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

final class WordSearchController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(Request $request, WordSearchAction $action): Response
    {
        $request->validate([
            'term' => 'string|required|min:2',
        ]);

        $html = view(
            'components.word-list',
            ['words' => $action->handle($request->term)]
        )->render();

        return new Response($html);
    }
}
