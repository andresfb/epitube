<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\EncodeErrorsAction;
use App\Actions\HasRejectedAction;
use App\Actions\MarkNotificationReadAction;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Throwable;

final class EncodeErrorsController extends Controller
{
    public function __construct(
        private readonly EncodeErrorsAction $errorsAction,
        private readonly HasRejectedAction $rejectedAction,
        private readonly MarkNotificationReadAction $notificationAction,
    ) {}

    public function __invoke(): Factory|View
    {
        // TODO: add a route/controller to list the rejected items

        try {
            $this->notificationAction->handle();
        } catch (Throwable $e) {
            Log::error("Error marking notifications as read: {$e->getMessage()}");
        }

        return view(
            'encoding.errors.index',
            [
                'errors' => $this->errorsAction->handle(),
                'rejected' => $this->rejectedAction->handle(),
            ]
        );
    }
}
