<?php

namespace App\Http\Controllers;

use App\Actions\VideoProgressAction;
use App\Dtos\Tube\VideoProgressItem;
use App\Http\Requests\VideoProgressRequest;
use App\Models\Tube\Content;
use Throwable;

class VideoProgressController extends Controller
{
    public function __invoke(VideoProgressRequest $request, Content $content, VideoProgressAction $action)
    {
        try {
            $action->handle(
                VideoProgressItem::from($request),
                $content,
            );

            return response()->noContent();
        } catch (Throwable $e) {
            return response(status: 500)->json([
                'data' => [
                    'status' => 500,
                    'message' => $e->getMessage(),
                ]
            ]);
        }
    }
}
