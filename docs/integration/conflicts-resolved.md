# WorkCore — Conflicts Resolved

## Conflict 1: `dashboard.user.team.*` route namespace collision

**Issue:** The existing host panel (`routes/panel.php`) defines `dashboard.user.team.*`
for the AI collaboration team feature (TeamController — invitations, workspace members).
WorkCore's HR/Staff module also needs `dashboard.user.team.*`.

**Resolution:** WorkCore HR/Team routes are prefixed `bos-team`:
- URL: `/dashboard/user/bos-team`
- Route name: `dashboard.user.bos-team.*`
- Menu items reference `bos-team` routes

**Impact:** No breaking change. Menu labels show "Team" visually but the route name is
`bos-team`. If the AI collaboration team feature is eventually retired or merged with the
BOS team module, rename to `dashboard.user.team.*` at that point.

---

## Conflict 2: WorkSuite `leads` vs. host `crm_leads` tables

**Issue:** WorkSuite has a `leads` table (from its CRM). Titan-BOS has `crm_leads`
(different schema, different controller). Both represent lead records but with different
column layouts.

**Resolution:**
- WorkSuite Lead model → `App\Models\WorkSuite\Crm\Lead` → table `leads`
- Host CrmLead model → `App\Models\Crm\CrmLead` → table `crm_leads`
- These are independent tables. Both can exist.
- WorkSuite migration creates `leads` table; host migration creates `crm_leads`.
- Menu surface: Host CRM `leads` → `dashboard.crm.leads.*`. WorkCore leads → `dashboard.user.leads.*`.

**Deferred:** In a later phase, decide whether to merge these into a single lead source
of truth or keep both (WorkSuite for operational CRM, host CRM for pipeline management).

---

## Conflict 3: WorkSuite `deals` vs. host `crm_deals`

Same pattern as leads conflict. Independent tables, different schemas, different controllers.

**Resolution:** Same as conflict 2 — keep both, deferred merge decision.

---

## Conflict 4: Tenancy column (`company_id` vs `user_id`)

**Issue:** WorkSuite models scope by `company_id`. Host CRM models scope by `user_id`.

**Resolution:**
- `config/worksuite.php` exposes `worksuite.tenancy.tenant_column` (env: `WORKSUITE_TENANT_COLUMN`).
- Default is `company_id` for compatibility with WorkSuite code.
- When wiring models, add a boot-time global scope that maps `company_id` from the
  authenticated user's primary company (or use `user_id` directly by changing the env var).
- No migration schema changes required — the resolution is at the query layer.

---

## Conflict 5: WorkSuite `User` references

WorkSuite models reference `App\Models\User` for `belongsTo` relationships. The host
`User` model is at `App\Models\User` — same path. No conflict; the relationship works
as-is because both codebases share the same Laravel `users` table.

---

## Conflict 6: `team_id` column on users

WorkSuite config references `team_id` (legacy column). The host user table has `team_id`
(added in migration `2024_01_30_063632`). No conflict; the column exists and WorkSuite
can reference it. The `config/worksuite.php` `legacy_team_column` setting documents this.

---

## Not a conflict: Host Finance vs. WorkCore Money

Host Finance (`app/Http/Controllers/Finance/`, `app/Models/Finance/`) handles:
- Subscription plans
- Payment gateways
- Platform token billing

WorkCore Money (`dashboard.user.money.*`) handles:
- Client invoices, estimates, expenses, credits (business operations)

These are completely separate concerns. No collision.

---

## Not a conflict: CRM module

Host `dashboard.crm.*` (native Titan CRM) and WorkCore `dashboard.user.customers.*` /
`dashboard.user.leads.*` serve different surfaces. Both can coexist. Long-term, the
native CRM may be replaced or unified with WorkCore's CRM domain.

---

## Conflict 7: Missing global helpers — `user()`, `company()`, `user_roles()`, `abort_403()`

**Issue (discovered via deep scan):** WorkSuite controllers rely on globally-available
helper functions that do not exist in MagicAI:

| Helper | WorkSuite usage | MagicAI equivalent |
|--------|-----------------|--------------------|
| `user()` | `user()->permission('view_invoices')` | `auth()->user()` |
| `company()` | `company()->id` for tenant scoping | `auth()->user()->companies()->first()` |
| `user_roles()` | `in_array('client', user_roles())` | Spatie `getRoleNames()` |
| `abort_403($bool)` | `abort_403(!$condition)` | `abort_if(!$condition, 403)` |

**Resolution:** `app/Support/worksuite_bridge.php` defines all four helpers as
conditional `function_exists` guards. Loaded by `WorkSuiteServiceProvider::boot()`.
No host code is modified; helpers only activate when the WorkSuite namespace is used.

---

## Conflict 8: Missing `AccountBaseController`

**Issue:** All 175 WorkSuite controllers extend `AccountBaseController`, which sets up
`$this->user`, `$this->company`, `$this->data[]`, module access checks, and view sharing.
This class does not exist in MagicAI.

**Resolution:** `app/Http/Controllers/WorkSuite/AccountBaseController.php` bridges this.
WorkCore BOS controllers extend this bridge class instead of the original.
Stub methods (`requireModule()`, `checkPermission()`) are no-ops during scaffold phase;
Phase 2 wires them to actual data.

---

## Conflict 9: Missing `CompanyScope` / `HasCompany` trait

**Issue (discovered via deep scan):** Every WorkSuite model that stores business data
uses the `HasCompany` trait, which registers `CompanyScope` as a global Eloquent scope.
This automatically filters all queries by `company_id`. Without it, WorkSuite models
would return unscoped data across all users.

**Resolution:**
- `app/Scopes/WorkSuite/CompanyScope.php` — Eloquent scope; filters by `company_id`
  resolved from `company()` bridge helper.
- `app/Traits/WorkSuite/HasCompany.php` — applies the scope on boot, auto-fills
  `company_id` on `creating`, provides `company()` relationship and `forCompany()` scope.

Apply to WorkSuite models as they are wired in Phase 2:
```php
use App\Traits\WorkSuite\HasCompany;

class Invoice extends Model {
    use HasCompany;
}
```

Bypass for admin/reporting queries:
```php
Invoice::withoutCompanyScope()->get();
```

---

## Conflict 10: WorkSuite `Reply::success()` AJAX helper

**Issue:** WorkSuite controllers return JSON via `Reply::success()`, `Reply::error()`,
and `Reply::dataOnly()`. This class doesn't exist in MagicAI.

**Resolution:** `app/Support/WorkSuiteReply.php` provides the `WorkSuiteReply` class
with identical method signatures. When porting WorkSuite controllers, replace:
```php
return Reply::success('messages.deleteSuccess');
// with:
return \App\Support\WorkSuiteReply::success(__('messages.deleteSuccess'));
```

---

## Conflict 11: Granular permission model (`all` / `added` / `owned` / `both`)

**Issue:** WorkSuite uses a 4-level per-resource permission model checked via
`user()->permission('view_invoices')` returning one of `'all'`, `'added'`, `'owned'`,
`'both'`. MagicAI uses Spatie Permission (role-based, boolean).

**Resolution (Phase 2):**
- Create a `WorkSuitePermissionService` that checks Spatie roles and maps to the
  WorkSuite 4-level model.
- Default during scaffold: `AccountBaseController::checkPermission()` is a no-op
  (all authenticated users pass).
- Document required permissions per domain in a permission seeder.

---

## ⚠ Critical Migration Risk: `2024_01_12_214740_worksuite_non_saas_to_saas_migration`

**Issue:** This migration adds `company_id` columns to ~50 existing tables and runs
a `NonSaasToSaasSeeder`. If executed against a database that already has those columns,
it will fail or cause duplicate data.

**Resolution:**
- This migration is inside `database/migrations/worksuite/` (not yet run).
- **DO NOT RUN** this migration on an existing Titan-BOS database.
- Instead, extract only the individual table-creation migrations for the business
  domain tables (invoices, employees, etc.) and run those individually.
- See `docs/integration/migration-actions.md` for the safe procedure.
