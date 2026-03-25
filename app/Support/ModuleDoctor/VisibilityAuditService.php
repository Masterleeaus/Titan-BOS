<?php
namespace App\Support\ModuleDoctor;

class VisibilityAuditService
{
    public function build(array $module = []): array
    {
        return $this->audit($module);
    }

    public function audit(array $module = []): array
    {
        return ['status' => 'ok', 'message' => 'Visibility audit active.'];
    }
}
