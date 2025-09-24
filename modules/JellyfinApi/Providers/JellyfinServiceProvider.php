<?php

declare(strict_types=1);

namespace Modules\JellyfinApi\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\JellyfinApi\Services\JellyfinService as JellyfinClient;

final class JellyfinServiceProvider extends ServiceProvider
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
        $this->app->singleton('jellyfin_client', static fn (): JellyfinClient => new JellyfinClient());
    }

    /**
     * Merges user's and jellyfin configs.
     */
    private function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/jellyfin.php', 'jellyfin');
    }
}
