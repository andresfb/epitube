<?php

namespace App\Http\Controllers;

use App\Actions\Frontend\TagsGetListAction;
use Illuminate\View\View;

class TagListController extends Controller
{
    public function __invoke(TagsGetListAction $action): View
    {
        return view(
            'tags.list',
            ['tagList' => $action->handle()],
        );
    }
}
