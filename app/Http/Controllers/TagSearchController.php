<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\TagSearchAction;
use App\Dtos\Tube\TagSearchItem;
use App\Http\Requests\TagSearchRequest;
use Illuminate\Http\Response;
use Throwable;

final class TagSearchController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(TagSearchRequest $request, TagSearchAction $action): Response
    {
        $html = view(
            'components.tag-list',
            ['tags' => $action->handle(TagSearchItem::from($request))]
        )->render();

        return new Response($html);
    }
}
