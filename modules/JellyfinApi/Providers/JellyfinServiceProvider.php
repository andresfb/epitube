<?php

namespace Modules\JellyfinApi\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\JellyfinApi\Services\JellyfinService as JellyfinClient;

class JellyfinServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerJellyfin();

        $this->mergeConfig();
    }

    private function registerJellyfin(): void
    {
        $this->app->singleton('jellyfin_client', static function () {
            return new JellyfinClient();
        });
    }

    /**
     * Merges user's and jellyfin configs.
     *
     * @return void
     */
    private function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/jellyfin.php', 'jellyfin');
    }
}
