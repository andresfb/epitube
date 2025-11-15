<?php

declare(strict_types=1);

use App\Http\Controllers\ContentController;
use App\Http\Controllers\DurationController;
use App\Http\Controllers\EncodeErrorsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SelectController;
use App\Http\Controllers\SwitchCategoryController;
use App\Http\Controllers\TaggedVideoController;
use App\Http\Controllers\TagListController;
use App\Http\Controllers\TagSearchController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoEngageController;
use App\Http\Controllers\VideoSearchController;
use App\Http\Controllers\VideoStatusController;
use App\Http\Controllers\WordSearchController;
use App\Models\Tube\Category;
use Illuminate\Support\Facades\Route;

Route::controller(TestController::class)->group(function () {
    Route::get('/tests', 'index')
        ->name('tests');

    Route::post('/tests/{slug}', 'update')
        ->name('tests.update');
});

Route::get('/', HomeController::class)->name('home');

Route::get('/duration/{duration}', DurationController::class)
    ->name('duration');

Route::get('/selects/{select}', SelectController::class)
    ->name('selects');

Route::get('/switch/{category}', SwitchCategoryController::class)
    ->name('switch.category')
    ->whereIn('category', Category::getSlugs());

Route::get('/videos/{slug}', VideoController::class)
    ->name('videos');

Route::controller(VideoEngageController::class)->group(function () {
    Route::post('/videos/{slug}/viewed', 'store')
        ->name('videos.viewed');

    Route::put('/videos/{slug}/progress', 'update')
        ->name('videos.progress');

    Route::delete('/videos/{slug}/disable', 'delete')
        ->name('videos.disable');
});

Route::controller(VideoStatusController::class)->group(function () {
    Route::post('/videos/{slug}/like', 'store')
        ->name('videos.like');

    Route::put('/videos/{slug}/feature', 'update')
        ->name('videos.feature');

    Route::delete('/videos/{slug}/dislike', 'delete')
        ->name('videos.dislike');
});

Route::controller(ContentController::class)->group(function () {
    Route::get('/contents', 'index')
        ->name('contents.list');

    Route::get('/contents/{slug}/edit', 'edit')
        ->name('contents.edit');

    Route::put('/contents', 'update')
        ->name('contents.update');
});

Route::get('/search', VideoSearchController::class)
    ->name('search');

Route::post('/search/words', WordSearchController::class)
    ->name('words.search');

Route::get('/tags/{slug}', TaggedVideoController::class)
    ->name('tag');

Route::get('/tags-list', TagListController::class)
    ->name('tags.list');

Route::post('/tags/', TagSearchController::class)
    ->name('tags.search');

Route::get('/encoding/errors', EncodeErrorsController::class)
    ->name('encoding.errors');
