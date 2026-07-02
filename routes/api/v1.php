<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\DurationController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\RandomVideosController;
use App\Http\Controllers\Api\V1\SelectController;
use App\Http\Controllers\Api\V1\SwitchCategoryController;
use App\Http\Controllers\Api\V1\TaggedVideoController;
use App\Http\Controllers\Api\V1\TagListController;
use App\Http\Controllers\Api\V1\TagSearchController;
use App\Http\Controllers\Api\V1\VideoController;
use App\Http\Controllers\Api\V1\VideoSearchController;
use App\Http\Controllers\Api\V1\WordSearchController;
use App\Http\Controllers\VideoEngageController;
use App\Http\Controllers\VideoStatusController;
use App\Models\Tube\Category;

Route::get('/', HomeController::class)
    ->name('home');

Route::get('/duration/{duration}', DurationController::class)
    ->name('duration');

Route::get('/selects/{select}', SelectController::class)
    ->name('selects');

Route::get('/random', RandomVideosController::class)
    ->name('random');

Route::get('/videos/{slug}', VideoController::class)
    ->name('videos');

Route::get('/tags/{slug}', TaggedVideoController::class)
    ->name('tag');

// No separate API controller for video engagement. The existing one works.
Route::controller(VideoEngageController::class)->group(function () {
    Route::post('/videos/{slug}/viewed', 'store')
        ->name('videos.viewed');

    Route::put('/videos/{slug}/progress', 'update')
        ->name('videos.progress');

    Route::delete('/videos/{slug}/disable', 'delete')
        ->name('videos.disable');
});

// No separate API controller for video status. The existing one works.
Route::controller(VideoStatusController::class)->group(function () {
    Route::post('/videos/{slug}/like', 'store')
        ->name('videos.like');

    Route::put('/videos/{slug}/feature', 'update')
        ->name('videos.feature');

    Route::delete('/videos/{slug}/dislike', 'delete')
        ->name('videos.dislike');
});

Route::controller(ContentController::class)->group(function () {
    Route::get('/contents/{slug}/edit', 'edit')
        ->name('contents.edit');

    Route::put('/contents', 'update')
        ->name('contents.update');
});

Route::get('/categories', CategoryController::class)
    ->name('categories');

Route::get('/switch/{category}', SwitchCategoryController::class)
    ->name('switch.category')
    ->whereIn('category', Category::getSlugs());

Route::get('/tags', TagListController::class)
    ->name('tags.list');

Route::get('/search', VideoSearchController::class)
    ->name('search');

Route::post('/search/words', WordSearchController::class)
    ->name('words.search');

Route::post('/search/tags', TagSearchController::class)
    ->name('tags.search');
