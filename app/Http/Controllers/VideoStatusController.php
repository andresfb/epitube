<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\ContentChangeStatusAction;
use App\Actions\Frontend\ContentFeatureAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

final class VideoStatusController extends Controller
{
    public function __construct(private readonly ContentChangeStatusAction $changeStatusAction) {}

    public function store(string $slug): JsonResponse
    {
        return $this->changeStatus(
            slug: $slug,
            status: 1
        );
    }

    public function update(ContentFeatureAction $action, string $slug)
    {
        try {
            $action->handle($slug);

            return response()->noContent();
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

    public function delete(string $slug): JsonResponse
    {
        return $this->changeStatus(
            slug: $slug,
            status: -1
        );
    }

    private function changeStatus(string $slug, int $status): JsonResponse
    {
        try {
            $likeStatus = $this->changeStatusAction->handle(
                slug: $slug,
                status: $status,
            );

            return response()->json([
                'data' => [
                    'status' => 200,
                    'like_status' => $likeStatus,
                    'message' => 'success',
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
