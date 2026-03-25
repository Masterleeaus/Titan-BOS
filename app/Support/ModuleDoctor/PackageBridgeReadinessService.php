<?php

namespace App\Support\ModuleDoctor;

class PackageBridgeReadinessService
{
    public function inspect(array $module): array
    {
        $bridge = app(PackageEntitlementAuditService::class)->inspect($module);
        $company = app(CompanyModuleSettingAuditService::class)->inspect($module);
        $registry = app(ModuleRegistryAuditService::class)->inspect($module);

        return [
            'bridge' => $bridge,
            'company' => $company,
            'registry' => $registry,
            'ready' => ($bridge['ready'] ?? false) && ($company['ready'] ?? false) && ($registry['ready'] ?? false),
        ];
    }
}
