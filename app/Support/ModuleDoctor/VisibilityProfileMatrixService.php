<?php

namespace App\Support\ModuleDoctor;

class VisibilityProfileMatrixService
{
    public function build(array $tenantResults): array
    {
        $matrix = [];
        foreach ($tenantResults as $profile => $result) {
            $matrix[] = [
                'profile' => $profile,
                'status' => $result['status'] ?? 'warn',
                'detail' => $result['detail'] ?? '',
            ];
        }

        return $matrix;
    }
}
