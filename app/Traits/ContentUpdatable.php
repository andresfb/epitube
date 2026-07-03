<?php

declare(strict_types=1);

namespace App\Traits;

use App\Actions\Frontend\ContentEditAction;
use App\Dtos\Tube\ContentEditItem;
use App\Http\Requests\ContentUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

trait ContentUpdatable
{
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
