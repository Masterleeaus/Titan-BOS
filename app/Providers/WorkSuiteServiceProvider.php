<?php

namespace App\Providers;

use App\Services\VerticalLanguageResolver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WorkSuiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(base_path('config/worksuite.php'), 'worksuite');
        $this->mergeConfigFrom(base_path('config/verticals.php'), 'verticals');

        $this->app->singleton(VerticalLanguageResolver::class, function () {
            return new VerticalLanguageResolver(
                config('worksuite.default_vertical', 'cleaning'),
                config('verticals', [])
            );
        });
    }

    public function boot(): void
    {
        // Surface-language helpers (worksuite_label())
        if (file_exists(base_path('app/Support/worksuite_helpers.php'))) {
            require_once base_path('app/Support/worksuite_helpers.php');
        }

        // Bridge helpers: user(), company(), user_roles(), abort_403()
        // These shim WorkSuite global functions that are absent in MagicAI.
        if (file_exists(base_path('app/Support/worksuite_bridge.php'))) {
            require_once base_path('app/Support/worksuite_bridge.php');
        }

        // Routes are loaded by RouteServiceProvider (web + auth middleware).
        // The duplicate registration below is intentionally removed to avoid
        // double-registration. See app/Providers/RouteServiceProvider.php.
    }
}
