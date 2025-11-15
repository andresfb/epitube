<?php

namespace App\Http\Controllers;

use App\Actions\Frontend\TagsSearchAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class TagsSearchController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(Request $request, TagsSearchAction $action)
    {
        $request->validate([
            'term' => 'string|required|min:2',
        ]);

        $html = view(
            'components.tag-list',
            ['tags' => $action->handle($request->term)]
        )->render();

        return new Response($html);
    }
}
