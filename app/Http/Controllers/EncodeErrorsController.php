<?php

namespace App\Http\Controllers;

use App\Actions\EncodeErrorsAction;
use App\Actions\HasRejectedAction;

class EncodeErrorsController extends Controller
{
    public function __construct(
        private readonly EncodeErrorsAction $errorsAction,
        private readonly HasRejectedAction $rejectedAction
    ) {}

    public function __invoke()
    {
        // TODO: add a route/controller to list the rejected items
        return view(
            'encoding.errors.index',
            [
                'errors' => $this->errorsAction->handle(),
                'rejected' => $this->rejectedAction->handle(),
            ]
        );
    }
}
