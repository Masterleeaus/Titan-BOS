<?php

namespace App\Traits\WorkSuite;

use App\Models\Company;
use App\Scopes\WorkSuite\CompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * HasCompany — WorkCore multi-tenancy trait.
 *
 * Apply this trait to any WorkSuite model that should be scoped to the current
 * company (tenant). It automatically:
 *   1. Registers CompanyScope as a global scope (all queries filtered by company_id)
 *   2. Sets company_id on new records created while a company context is active
 *   3. Provides a company() relationship
 *
 * Usage:
 *   class Invoice extends Model {
 *       use \App\Traits\WorkSuite\HasCompany;
 *   }
 *
 * To query across all companies (admin/reporting):
 *   Invoice::withoutCompanyScope()->get();
 */
trait HasCompany
{
    protected static function bootHasCompany(): void
    {
        static::addGlobalScope(new CompanyScope());

        // Auto-fill company_id on new records when a company context is active.
        static::creating(function ($model) {
            $tenantColumn = config('worksuite.tenancy.tenant_column', 'company_id');

            if (empty($model->{$tenantColumn}) && function_exists('company') && company()) {
                $model->{$tenantColumn} = company()->id;
            }
        });
    }

    /**
     * Relationship to the owning Company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope: filter by a specific company (bypasses the global scope).
     */
    public function scopeForCompany($query, int $companyId)
    {
        $tenantColumn = config('worksuite.tenancy.tenant_column', 'company_id');

        return $query->withoutGlobalScope(CompanyScope::class)
                     ->where($this->getTable() . '.' . $tenantColumn, $companyId);
    }
}
