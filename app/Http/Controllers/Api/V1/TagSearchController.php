<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Frontend\TagSearchAction;
use App\Dtos\Tube\TagSearchItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\TagSearchRequest;
use Illuminate\Http\JsonResponse;

class TagSearchController extends Controller
{
    public function __invoke(TagSearchRequest $request, TagSearchAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->handle(TagSearchItem::from($request)),
        ]);
    }
}
