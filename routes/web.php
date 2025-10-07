<?php

declare(strict_types=1);

use App\Http\Controllers\EncodeErrorsController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/encoding/errors', EncodeErrorsController::class)
    ->name('encoding.errors');
