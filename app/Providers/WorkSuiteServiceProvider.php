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
        if (file_exists(base_path('app/Support/worksuite_helpers.php'))) {
            require_once base_path('app/Support/worksuite_helpers.php');
        }

        if (file_exists(base_path('routes/worksuite.php'))) {
            Route::middleware(config('worksuite.middleware', ['auth']))
                ->group(base_path('routes/worksuite.php'));
        }
    }
}
