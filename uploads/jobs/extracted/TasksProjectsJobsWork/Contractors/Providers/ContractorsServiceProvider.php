<?php

namespace Modules\Contractors\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

class ContractorsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'contractors');
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Contractors\Console\ContractorsSelfTestCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        if (file_exists(__DIR__.'/../Routes/api.php')) $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        if (file_exists(__DIR__.'/../Routes/api_sanctum.php')) $this->loadRoutesFrom(__DIR__.'/../Routes/api_sanctum.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'contractors');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'contractors');
        Blade::componentNamespace('Modules\\Contractors\\Resources\\views\\components', 'contractors');

        // Policies (safe defaults; adjust as needed)
        if (class_exists(\Modules\Contractors\Entities\Contractor::class)) {
            Gate::policy(\Modules\Contractors\Entities\Contractor::class, \Modules\Contractors\Policies\ContractorPolicy::class);
        }
    }
}
