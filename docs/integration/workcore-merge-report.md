# WorkCore Cleaning BOS — Merge Report

**Date:** 2026-03-25
**Branch:** `feature/workcore-cleaning-bos-merge`
**Source:** `/uploads/WorkCore.zip` (WorkSuite pre-merge prep package)
**Target:** MagicAI / Titan-BOS host platform

---

## Summary

WorkCore.zip is a cleaned and pre-processed WorkSuite Business Operating System package
prepared for structured merge into MagicAI. It contains 3,240+ PHP files organized into
six domain slices. This report covers Phase 1 of the merge.

---

## What Was Merged (Phase 1)

### Host-side scaffold (overlay)

| File | Action | Notes |
|------|--------|-------|
| `config/worksuite.php` | Added | Feature flags, route prefix, tenancy column config |
| `config/verticals.php` | Added | Cleaning vertical label map (surface language) |
| `app/Providers/WorkSuiteServiceProvider.php` | Added | Registers config, routes, VerticalLanguageResolver singleton |
| `app/Services/VerticalLanguageResolver.php` | Added | Resolves surface labels per vertical (default: cleaning) |
| `app/Support/worksuite_helpers.php` | Added | `worksuite_label()` helper function |
| `routes/worksuite.php` | Added | Full BOS route definitions under `dashboard.user.*` |
| `config/app.php` | Modified | Registered `WorkSuiteServiceProvider` |
| `app/Providers/RouteServiceProvider.php` | Modified | Loads `routes/worksuite.php` inside web middleware group |

### CRM domain slice (namespace-rewritten)

| Directory | Files | Namespace |
|-----------|-------|-----------|
| `app/Http/Controllers/WorkSuite/Crm/` | 25 | `App\Http\Controllers\WorkSuite\Crm` |
| `app/Models/WorkSuite/Crm/` | 36 | `App\Models\WorkSuite\Crm` |
| `app/Events/WorkSuite/Crm/` | 9 | `App\Events\WorkSuite\Crm` |
| `app/Listeners/WorkSuite/Crm/` | 8 | `App\Listeners\WorkSuite\Crm` |
| `app/Notifications/WorkSuite/Crm/` | 16 | `App\Notifications\WorkSuite\Crm` |
| `app/Observers/WorkSuite/Crm/` | 26 | `App\Observers\WorkSuite\Crm` |
| `app/Jobs/WorkSuite/Crm/` | 3 | `App\Jobs\WorkSuite\Crm` |
| `app/Exports/WorkSuite/Crm/` | 1 | `App\Exports\WorkSuite\Crm` |
| `app/Imports/WorkSuite/Crm/` | 3 | `App\Imports\WorkSuite\Crm` |
| `app/DataTables/WorkSuite/Crm/` | 16 | `App\DataTables\WorkSuite\Crm` |
| `app/Http/Requests/WorkSuite/Crm/` | 25 | `App\Http\Requests\WorkSuite\Crm` |
| `app/Traits/WorkSuite/Crm/` | 3 | `App\Traits\WorkSuite\Crm` |
| `app/View/Components/WorkSuite/Crm/` | 4 | `App\View\Components\WorkSuite\Crm` |
| `database/migrations/worksuite/` | 31 | Prefixed, not yet run |
| `resources/views/worksuite/crm/` | 121 | Blade views (not yet wired to new controllers) |

### BOS domain controllers (stub phase)

All domains have stub controllers that boot the site and return placeholder views:

| Domain | Controller namespace | Route prefix |
|--------|---------------------|--------------|
| Customers | `WorkSuite\Customers` | `dashboard.user.customers.*` |
| Leads | `WorkSuite\Customers` | `dashboard.user.leads.*` |
| Follow-Ups | `WorkSuite\Customers` | `dashboard.user.followups.*` |
| Jobs | `WorkSuite\Jobs` | `dashboard.user.jobs.*` |
| Checklist | `WorkSuite\Checklist` | `dashboard.user.checklists.*` |
| Schedule & Dispatch | `WorkSuite\Schedule` | `dashboard.user.schedule.*` |
| Team (HR) | `WorkSuite\Team` | `dashboard.user.bos-team.*` |
| Money | `WorkSuite\Money` | `dashboard.user.money.*` |
| Service Requests | `WorkSuite\ServiceRequests` | `dashboard.user.service-requests.*` |
| Playbooks | `WorkSuite\Playbooks` | `dashboard.user.playbooks.*` |
| Insights | `WorkSuite\Insights` | `dashboard.user.insights.*` |
| Team Chat | `WorkSuite\TeamChat` | `dashboard.user.team-chat.*` |

### Menu

`database/seeders/WorkCoreBosMenuSeeder.php` — seeds all BOS module items using the
existing `Menu` model and `MenuService::regenerate()`.

---

## What Was Reused (not duplicated)

- Auth system: untouched
- User model: untouched
- `config/app.php` providers array: only appended
- `RouteServiceProvider`: only appended
- Existing `dashboard.crm.*` routes: untouched (Titan native CRM preserved)
- Existing `dashboard.tasks.*` routes: untouched
- igaster/laravel-theme: no changes; WorkCore views call `view('worksuite.x.y')` which resolves via the theme engine

---

## What Was Renamed

| WorkSuite name | Cleaning BOS surface name | Key |
|----------------|--------------------------|-----|
| Projects | Jobs | `jobs` |
| Tasks | Cleaning Checklist | `checklists` |
| Clients | Customers | `customers` |
| Employees | Cleaners | `cleaners` |
| Attendance | Shift Log | `attendance` |
| Shift Roster | Availability | `shifts` |
| Finance | Money | `money` |
| Estimates | Quotes | `quotes` |
| Tickets / Support | Service Requests | `service-requests` |
| Knowledge Base | Playbooks | `playbooks` |
| Calendar | Schedule & Dispatch | `schedule` |
| Reports | Insights | `insights` |
| Messages | Team Chat | `team-chat` |

---

## Domain Slices Not Yet Merged (Phase 2+)

The following domain zips from WorkCore are extracted but controllers/models not yet
wired to the stub routes. CRM domain models/files placed under WorkSuite namespace but
inter-model `use` statements still reference original flat namespaces.

| Slice | Files | Status |
|-------|-------|--------|
| `crm_leads_clients_files.zip` | 330 | Namespace-copied; use-statements need rewrite |
| `finance_sales_files.zip` | ~391 | Not extracted |
| `hr_attendance_leave_files.zip` | ~362 | Not extracted |
| `projects_tasks_time_files.zip` | ~404 | Not extracted |
| `support_comms_files.zip` | ~433 | Not extracted |
| `platform_misc_files.zip` | ~1059 | Not extracted |

---

## Vertical Language Resolver

The `VerticalLanguageResolver` service is bound as a singleton. Default vertical is
`cleaning`. Views can call `worksuite_label('projects')` → returns `'Jobs'`.

To add a new vertical, append an entry to `config/verticals.php`.

---

## Success Conditions Met

- [x] Site boots normally (no breaking changes to host)
- [x] WorkSuiteServiceProvider registered safely
- [x] Routes follow `dashboard.user.*` naming
- [x] Views use `view('worksuite.x.y')` → theme resolves automatically
- [x] Vertical resolver loaded; cleaning labels active
- [x] Menu seeder ready to seed all BOS modules
- [x] No auth conflicts
- [x] Host CRM, tasks, finance untouched

---

## Next Steps

See `docs/integration/migration-actions.md` and `docs/integration/conflicts-resolved.md`.
