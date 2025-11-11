<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\ContentEditAction;
use App\Actions\Frontend\ContentGetAction;
use App\Actions\Frontend\ContentListAction;
use App\Dtos\Tube\ContentEditItem;
use App\Dtos\Tube\ContentListItem;
use App\Factories\ContentItemFactory;
use App\Http\Requests\ContentListRequest;
use App\Http\Requests\ContentUpdateRequest;
use App\Models\Tube\Category;
use App\Models\Tube\Tag;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

final class ContentController extends Controller
{
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

    public function update(ContentUpdateRequest $request, ContentEditAction $action): JsonResponse
    {
        try {
            $contentItem = $action->handle(ContentEditItem::from($request));

            return response()->json([
                'data' => [
                    'status' => 200,
                    'message' => 'Content updated successfully',
                    'content' => $contentItem,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return response()->json([
                'data' => [
                    'status' => 500,
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }
}
