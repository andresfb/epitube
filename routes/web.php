<?php

declare(strict_types=1);

use App\Http\Controllers\EncodeErrorsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SwitchCategoryController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/switch/{category}', SwitchCategoryController::class)
    ->name('switch.category')
    ->whereIn('category', Category::getSlugs());

Route::get('/encoding/errors', EncodeErrorsController::class)
    ->name('encoding.errors');
