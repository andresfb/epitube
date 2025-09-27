<?php

declare(strict_types=1);

use App\Http\Controllers\EncodeErrorsController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get('/', static function (): View {
    return view('welcome');
})->name('home');

Route::get('/encoding/errors', EncodeErrorsController::class)
    ->name('encoding.errors');
