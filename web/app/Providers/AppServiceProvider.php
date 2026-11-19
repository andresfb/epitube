<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\FeedMirror;
use App\Models\Tube\Category;
use App\Models\Tube\Tag;
use App\Services\Tube\Feed\FeedMirrorService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ConcreteView;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('category', fn ($app): Collection => collect());
        $this->app->bind(FeedMirror::class, FeedMirrorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        $this->configureModels();
        $this->configureVite();

        if ($this->app->isLocal()) {
            URL::forceScheme('http');
        } else {
            URL::forceScheme('https');
        }

        Gate::define('viewApiDocs', static function (): bool {
            return true;
        });

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
