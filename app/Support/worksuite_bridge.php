<?php

/**
 * WorkCore Bridge Helpers
 * ──────────────────────
 * WorkSuite source code calls several global helper functions that exist in
 * the WorkSuite host (worksuite/worksuite) but are NOT present in MagicAI.
 *
 * This file shims those helpers so WorkCore controllers/models boot without
 * fatal errors. These are thin bridges — replace with proper implementations
 * when wiring actual model/controller logic.
 *
 * Functions bridged:
 *   user()          → auth()->user()
 *   company()       → company context resolver (stub — Phase 2 wires this)
 *   user_roles()    → spatie roles via auth user
 *   Reply::success  → JSON response helper
 *   abort_403()     → abort(403, ...) shorthand
 */

// ── user() ────────────────────────────────────────────────────────────────────
// WorkSuite calls user() everywhere instead of auth()->user().
if (! function_exists('user')) {
    function user(): ?\App\Models\User
    {
        return auth()->user();
    }
}

// ── company() ─────────────────────────────────────────────────────────────────
// WorkSuite resolves the current tenant company from session/middleware.
// In MagicAI, company_id maps to the authenticated user's primary company.
//
// Phase 2 TODO: Replace stub with proper company resolution via a middleware
// that sets app('worksuite.company') from the user's company record.
if (! function_exists('company')) {
    function company(): ?\App\Models\Company
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        // Try cached instance first (set by WorkSuiteCompanyMiddleware in Phase 2)
        if (app()->bound('worksuite.company')) {
            return app('worksuite.company');
        }

        // Fallback: resolve from user's first company record
        $company = $user->companies()->first();

        if ($company) {
            app()->instance('worksuite.company', $company);
        }

        return $company;
    }
}

// ── user_roles() ─────────────────────────────────────────────────────────────
// WorkSuite returns an array of role slugs for the current user.
// MagicAI uses Spatie Permission; we bridge via getRoleNames().
if (! function_exists('user_roles')) {
    function user_roles(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        // Map MagicAI Roles enum to WorkSuite role slugs
        $roles = $user->getRoleNames()->toArray();

        // If Spatie roles empty, fall back to the type enum
        if (empty($roles) && method_exists($user, 'isAdmin')) {
            if ($user->isSuperAdmin()) {
                $roles[] = 'admin';
            } elseif ($user->isAdmin()) {
                $roles[] = 'manager';
            } else {
                $roles[] = 'employee';
            }
        }

        return $roles;
    }
}

// ── abort_403() ───────────────────────────────────────────────────────────────
// WorkSuite uses abort_403($condition) to throw 403 when condition is true.
// Laravel has abort_if(); this shims the WorkSuite convention.
if (! function_exists('abort_403')) {
    function abort_403(bool $condition, string $message = 'This action is unauthorized.'): void
    {
        if ($condition) {
            abort(403, $message);
        }
    }
}
