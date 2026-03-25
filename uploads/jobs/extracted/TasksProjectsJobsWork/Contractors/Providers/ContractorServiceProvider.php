<?php

namespace Modules\Contractors\Providers;

use Illuminate\Support\ServiceProvider;

class ContractorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'contractor');
    }

    public function boot()

        // Config-driven menu + permissions
        try {
            // Auto-seed permissions if spatie/laravel-permission is available
            if (config('contractor.permissions.auto_seed_permissions') === true && class_exists('Spatie\\Permission\\Models\\Permission')) {
                $abilities = (array) config('contractor.permissions.abilities', []);
                foreach ($abilities as $slug) {
                    if (!\Spatie\Permission\Models\Permission::where('name', $slug)->exists()) {
                        \Spatie\Permission\Models\Permission::create(['name' => $slug]);
                    }
                }
            }
        } catch (\Throwable $e) { /* noop */ }

        try {
            $driver = config('contractor.menu.driver', 'app_menu');
            $item = (array) config('contractor.menu.item');
            if ($driver === 'app_menu' && class_exists('App\\Menu')) {
                \App\Menu::add($item);
            } elseif ($driver === 'container_menu' && function_exists('app') && app()->bound('menu')) {
                app('menu')->add($item);
            }
        } catch (\Throwable $e) { /* noop */ }

    {
        // Sanctum-aware API route loading
        try {
            if (class_exists('Laravel\\Sanctum\\Sanctum') && file_exists(__DIR__.'/../Routes/api_sanctum.php')) {
                $this->loadRoutesFrom(__DIR__.'/../Routes/api_sanctum.php');
            }
        } catch (\Throwable $e) { /* noop */ }


        // --- Worksuite menu integration (latest variants, guarded) ---
        try {
            // Variant A: Global Menu facade/service
            if (class_exists('App\\Menu')) {
                \App\Menu::add([
                    'slug' => 'contractors',
                    'title' => 'Contractors',
                    'icon' => 'fa fa-user-tie',
                    'route' => 'contractor.index',
                    'permission' => 'contractor.manage'
                ]);
            }
            // Variant B: Container-bound menu service
            else if (function_exists('app') && app()->bound('menu')) {
                app('menu')->add([
                    'slug' => 'contractors',
                    'title' => 'Contractors',
                    'icon' => 'fa fa-user-tie',
                    'route' => 'contractor.index',
                    'permission' => 'contractor.manage'
                ]);
            }
        } catch (\Throwable $e) { /* noop */ }

        // --- Optional Sidebar/Menu wiring (guarded) ---
        try {
            // Common pattern: if Worksuite exposes a Menu facade/service, call it here.
            // We guard with class_exists to avoid breaking builds that differ.
            if (class_exists('App\\Menu')) {
                \App\Menu::add([
                    'slug' => 'contractor',
                    'title' => 'Contractors',
                    'icon' => 'fa fa-users',
                    'route' => 'contractor.index',
                    'permission' => null
                ]);
            }
        } catch (\Throwable $e) {
            // Silent if menu system differs. You can wire manually into your theme/sidebar.
        }

        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'contractor');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'contractor');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}