<?php

namespace App\Support\ModuleDoctor;

class PermissionReadinessService
{
    public function inspect(array $module): array
    {
        $keys = app(PermissionUsageExtractorService::class)->extract($module['files'] ?? []);
        $roleMatrix = app(PermissionPivotMatrixService::class)->matrix($keys);

        return [
            'keys' => $keys,
            'missing_keys' => array_values(array_filter($keys, fn ($key) => empty($roleMatrix[$key]['exists']))),
            'unmapped_keys' => array_values(array_filter($keys, fn ($key) => empty($roleMatrix[$key]['roles']))),
            'ready' => !empty($keys),
            'matrix' => $roleMatrix,
        ];
    }
}
