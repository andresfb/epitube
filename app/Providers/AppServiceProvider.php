<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share(
            'category',
            session(
                'category',
                Config::string('constants.main_category')
            ),
        );

        View::share('categories', Category::getRouterList());
    }
}
