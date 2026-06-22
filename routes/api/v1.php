<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\HomeController;

Route::get('/', HomeController::class)->name('home');
