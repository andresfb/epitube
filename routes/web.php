<?php

declare(strict_types=1);

use App\Http\Controllers\ContentController;
use App\Http\Controllers\EncodeErrorsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SwitchCategoryController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoStatusController;
use App\Models\Tube\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/switch/{category}', SwitchCategoryController::class)
    ->name('switch.category')
    ->whereIn('category', Category::getSlugs());

Route::get('/videos/{slug}', VideoController::class)
    ->name('videos');

Route::controller(VideoStatusController::class)->group(function () {
    Route::get('/videos/{slug}/viewed', 'viewed')
        ->name('videos.viewed');

    Route::post('/videos/{slug}/like', 'like')
        ->name('videos.like');

    Route::put('/videos/{slug}/dislike', 'dislike')
        ->name('videos.dislike');

    Route::patch('/videos/{slug}/progress', 'progress')
        ->name('videos.progress');

    Route::delete('/videos/{slug}/disable', 'disable')
        ->name('videos.disable');
});

Route::controller(ContentController::class)->group(function () {
    Route::get('/contents', 'index')
        ->name('contents.list');

    Route::get('/contents/{slug}/edit', 'edit')
        ->name('contents.edit');

    Route::put('/contents', 'update')
        ->name('contents.update');
});

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
