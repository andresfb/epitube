<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\ContentGetAction;
use App\Actions\Frontend\ContentListAction;
use App\Dtos\Tube\ContentListItem;
use App\Http\Requests\ContentListRequest;
use App\Http\Requests\ContentUpdateRequest;
use App\Models\Tube\Category;
use App\Models\Tube\Content;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

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
        ]);
    }

    public function update(ContentUpdateRequest $request, string $slug): JsonResponse
    {
        try {
            $content = Content::query()->where('slug', $slug)->firstOrFail();

            $content->update([
                'title' => $request->validated('title'),
                'slug' => $request->validated('slug'),
                'category_id' => $request->validated('category_id'),
                'service_url' => $request->validated('service_url'),
                'active' => $request->boolean('active'),
            ]);

            return response()->json([
                'data' => [
                    'status' => 200,
                    'message' => 'Content updated successfully',
                ],
            ]);
        } catch (Exception $e) {
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
