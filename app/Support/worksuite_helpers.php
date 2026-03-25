<?php

use App\Services\VerticalLanguageResolver;

if (! function_exists('worksuite_label')) {
    function worksuite_label(string $key, ?string $fallback = null): string
    {
        if (! function_exists('app')) {
            return $fallback ?? $key;
        }

        return app(VerticalLanguageResolver::class)->label($key, $fallback);
    }
}
