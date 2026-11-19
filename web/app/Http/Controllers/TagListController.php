<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\TagGetListAction;
use Illuminate\View\View;

final class TagListController extends Controller
{
    public function __invoke(TagGetListAction $action): View
    {
        return view(
            'tags.list',
            ['tagList' => $action->handle()],
        );
    }
}
