<?php

declare(strict_types=1);

use App\Http\Controllers\ContentController;
use App\Http\Controllers\EncodeErrorsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SwitchCategoryController;
use App\Http\Controllers\VideosController;
use App\Models\Tube\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/switch/{category}', SwitchCategoryController::class)
    ->name('switch.category')
    ->whereIn('category', Category::getSlugs());

Route::get('/videos/{slug}', VideosController::class)->name('video');

Route::get('/contents', [ContentController::class, 'index'])
    ->name('content.list');

// TODO: add the controller to load the Contents from a giving tag
Route::get('/tags/{slug}', static function (string $slug) {
    echo $slug;
})->name('tags');

// TODO: add the controller to list all the tags (with a tally of how many contents they have)
Route::get('/tags/list', static function () {
    echo 'tag list';
})->name('tags.list');

Route::get('/encoding/errors', EncodeErrorsController::class)
    ->name('encoding.errors');
