<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Tube\Category;
use App\Models\Tube\Tag;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\View as ConcreteView;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('category', fn ($app) => collect());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        $this->configureModels();
        $this->configureVite();

        if ($this->app->isProduction()) {
            $this->app['request']->server->set('HTTPS','on');
            URL::forceScheme('https');
        } else {
            URL::forceScheme('http');
        }

        View::composer('components.navbar', static function (ConcreteView $view) {
            $slug = Session::get(
                'category',
                Config::string('constants.main_category')
            );

            $view->with([
                'category' => Category::getName($slug),
                'icon' => Category::getIcon($slug),
                'categories' => Category::getRouterList(),
                'tags' => Tag::getMenuList($slug),
            ]);
        });
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction(),
        );
    }

    /**
     * Configure the application's models.
     */
    private function configureModels(): void
    {
        Model::unguard();
        Model::shouldBeStrict();
    }

    /**
     * Configure the application's Vite instance.
     */
    private function configureVite(): void
    {
        Vite::useAggressivePrefetching();
    }
}
