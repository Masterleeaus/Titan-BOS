<?php

namespace Modules\CRMCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next)
    {
        // Hook your tenancy resolver here (subdomain/header/JWT claim).
        return $next($request);
    }
}
