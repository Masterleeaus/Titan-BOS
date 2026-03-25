<?php

namespace Modules\CRMCore\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class CRMCoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        // PSR-4 shim for donor namespaces → Modules/CRMCore/Donor/*
        if (strncmp($class, $prefix, $len) !== 0) {
                    continue;
                }
                $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $len));
                $file = __DIR__ . '/../' . $baseDir . $relative . '.php';
                if (file_exists($file)) {
                    require $file;
                    return true;
                }
            }
            return false;
        });
    }

    public function boot()
    {
        // Load donor routes if present
        $donorBase = __DIR__ . '/../Donor';
        $web = $donorBase . '/routes/web.php';
        $api = $donorBase . '/routes/api.php';
        if (file_exists($web)) { require $web; }
        if (file_exists($api)) { require $api; }

        // Load donor migrations and views (namespaced as 'crmcore')
        $migrations = $donorBase . '/database/migrations';
        if (is_dir($migrations)) { $this->loadMigrationsFrom($migrations); }

        $views = $donorBase . '/resources/views';
        if (is_dir($views)) { $this->loadViewsFrom($views, 'crmcore'); }

        // Optionally merge donor config files (safe, non-destructive)
        $configDir = $donorBase . '/config';
        if (is_dir($configDir)) {
            foreach (glob($configDir . '/*.php') as $cfg) {
                $name = basename($cfg, '.php');
                $this->mergeConfigFrom($cfg, $name);
            }
        }
    }
}
