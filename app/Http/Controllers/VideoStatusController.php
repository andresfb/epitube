<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\ContentChangeStatusAction;
use App\Actions\Frontend\ContentDisableAction;
use App\Actions\Frontend\ContentViewedAction;
use App\Dtos\Tube\VideoProgressItem;
use App\Http\Requests\VideoProgressRequest;
use App\Jobs\Tube\VideoProgressJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

final class VideoStatusController extends Controller
{
    public function __construct(private readonly ContentChangeStatusAction $changeStatusAction) {}

    public function viewed(ContentViewedAction $action, string $slug)
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

    public function like(string $slug): JsonResponse
    {
        return $this->changeStatus(
            slug: $slug,
            status: 1
        );
    }

    public function dislike(string $slug): JsonResponse
    {
        return $this->changeStatus(
            slug: $slug,
            status: -1
        );
    }

    public function disable(ContentDisableAction $action, string $slug)
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

    public function progress(VideoProgressRequest $request, string $slug)
    {
        try {
            VideoProgressJob::dispatch(
                $slug,
                VideoProgressItem::from($request)
            );

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
