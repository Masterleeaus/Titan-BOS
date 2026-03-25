<?php

namespace App\Support\ModuleDoctor;

class RouteReadinessService
{
    public function inspect(array $module): array
    {
        $declared = app(RouteReferenceExtractorService::class)->extract($module['files'] ?? []);
        $registered = app(RegisteredRouteCatalogService::class)->all();

        $missing = [];
        foreach ($declared as $name) {
            if (!in_array($name, $registered, true)) {
                $missing[] = $name;
            }
        }

        return [
            'declared' => $declared,
            'registered_count' => count($registered),
            'missing' => $missing,
            'ready' => empty($missing),
        ];
    }
}
