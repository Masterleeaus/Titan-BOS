<?php

use Illuminate\Support\Facades\Route;
use Modules\CRMCore\Http\Middleware\EnsureTenantContext;

Route::prefix('api/crmcore/v1')
    ->middleware(['api', EnsureTenantContext::class])
    ->group(function () {
        // Add your CRMCore REST endpoints here (apply can:crmcore.* as needed)
    });
