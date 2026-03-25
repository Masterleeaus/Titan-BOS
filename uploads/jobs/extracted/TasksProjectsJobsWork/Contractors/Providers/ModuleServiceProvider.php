<?php

namespace Modules\Contractors\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'contractor');
        $this->mergeConfigFrom(__DIR__.'/../Config/permissions.php', 'contractor.permissions');
        if (file_exists(__DIR__.'/../Config/ai.php')) {
            $this->mergeConfigFrom(__DIR__.'/../Config/ai.php', 'contractor.ai');
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'contractor');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Optional: publish config
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('contractor.php'),
        ], 'contractor-config');
    }
}
