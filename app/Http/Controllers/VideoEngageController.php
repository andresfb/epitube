<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Frontend\ContentDisableAction;
use App\Actions\Frontend\ContentViewedAction;
use App\Dtos\Tube\VideoProgressItem;
use App\Http\Requests\VideoProgressRequest;
use App\Jobs\Tube\VideoProgressJob;
use Illuminate\Support\Facades\Log;
use Throwable;

final class VideoEngageController extends Controller
{
    /**
     * Mark video as viewed
     */
    public function store(ContentViewedAction $action, string $slug)
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

    /**
     * Update video progress
     */
    public function update(VideoProgressRequest $request, string $slug)
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

    /**
     * Disable video
     */
    public function delete(ContentDisableAction $action, string $slug)
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
}
