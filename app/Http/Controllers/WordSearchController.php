<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\WordSearchAction;
use App\Dtos\Tube\WordSearchItem;
use App\Http\Requests\WordSearchRequest;
use Illuminate\Http\Response;
use Throwable;

final class WordSearchController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(WordSearchRequest $request, WordSearchAction $action): Response
    {
        $html = view(
            'components.word-list',
            ['words' => $action->handle(WordSearchItem::from($request))]
        )->render();

        return new Response($html);
    }
}
