<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Frontend\WordSearchAction;
use App\Dtos\Tube\WordSearchItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\WordSearchRequest;
use Illuminate\Http\JsonResponse;

class WordSearchController extends Controller
{
    public function __invoke(WordSearchRequest $request, WordSearchAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->handle(WordSearchItem::from($request))
                ->pluck('word'),
        ]);
    }
}
