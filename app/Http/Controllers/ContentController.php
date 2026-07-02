<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\ContentGetAction;
use App\Actions\Frontend\ContentListAction;
use App\Dtos\Tube\ContentListItem;
use App\Factories\ContentItemFactory;
use App\Http\Requests\ContentListRequest;
use App\Models\Tube\Category;
use App\Models\Tube\Tag;
use App\Traits\ContentUpdatable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

final class ContentController extends Controller
{
    use ContentUpdatable;

    /**
     * @throws Exception
     */
    public function index(ContentListRequest $request, ContentListAction $action): View
    {
        $contents = $action->handle(ContentListItem::from($request));

        return view('content.list', ['contents' => $contents]);
    }

    public function edit(ContentGetAction $action, string $slug): JsonResponse|View
    {
        return view('content.edit-form', [
            'content' => $action->handle($slug),
            'categories' => Category::all(),
            'tags' => ContentItemFactory::prepareTags(Tag::getList()),
        ]);
    }
}
