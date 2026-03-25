<?php

namespace Modules\CRMCore\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = request()?->user()?->tenant_id ?? null;
            if ($tenantId !== null) {
                $builder->where((new static)->getTable().'.tenant_id', $tenantId);
            }
        });
    }
}
