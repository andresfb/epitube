<?php

namespace App\Http\Controllers;

use App\Actions\ContentListAction;
use App\Dtos\Tube\ContentListItem;
use App\Http\Requests\ContentListRequest;
use Exception;
use Illuminate\View\View;

class ContentController extends Controller
{
    /**
     * @throws Exception
     */
    public function index(ContentListRequest $request, ContentListAction $action): View
    {
        $contents = $action->handle(ContentListItem::from($request));

        return view('content.list', compact('contents'));
    }
}
