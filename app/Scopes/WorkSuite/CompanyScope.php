<?php

namespace App\Scopes\WorkSuite;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * CompanyScope — WorkCore multi-tenancy enforcement.
 *
 * WorkSuite automatically scopes every model query to the current company via
 * this global scope (applied by the HasCompany trait).
 *
 * In MagicAI/Titan-BOS, "company" maps to the authenticated user's primary
 * Company record. If no company is resolved (e.g. unauthenticated requests),
 * the scope is skipped so the query doesn't silently return nothing.
 *
 * Tenancy column is read from config('worksuite.tenancy.tenant_column'),
 * defaulting to 'company_id'.
 */
class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->hasUser()) {
            return;
        }

        $company = function_exists('company') ? company() : null;

        if (! $company) {
            return;
        }

        $tenantColumn = config('worksuite.tenancy.tenant_column', 'company_id');
        $table        = $model->getTable();

        $builder->where("{$table}.{$tenantColumn}", '=', $company->id);
    }

    public function extend(Builder $builder): void
    {
        // Allow callers to bypass the scope when needed:
        //   Model::withoutCompanyScope()->get();
        $builder->macro('withoutCompanyScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
