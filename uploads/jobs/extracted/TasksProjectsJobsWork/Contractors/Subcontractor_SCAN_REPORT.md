# Subcontractor nWidart Module — Static Scan Report

## Summary

- Extracted from: `SubcontractorModule_nWidart_FULL.zip`
- Output dir: `/mnt/data/SubcontractorModule_nWidart_FULL`
- Total files: **153**
- MANIFEST: `/mnt/data/SubcontractorModule_nWidart_FULL/MANIFEST.sha256`

## nWidart Anatomy

- module.json files found: **1**
  - 1) `module.json` → keys: ['active', 'alias', 'aliases', 'description', 'files', 'keywords', 'name', 'order', 'priority', 'providers']

- composer.json files found: **0**

## Key Components Detected
- Providers: 0
- Routes: 0
- Migrations: 0
- Configs: 0
- Views: 0
- Translations: 0
- Seeders: 0
- Controllers: 0
- Entities/Models: 0

## Checks
- ✅ **module.json presence** — PASS. nWidart modules require a module.json in the module root.
- ⚠️ **ServiceProvider(s) present** — WARN. Ensure the primary ModuleServiceProvider registers routes, views, translations, config.
- ⚠️ **routes** — WARN. If there are no routes, confirm this is a headless module (console/jobs only).
- ℹ️ **migrations** — INFO. Optional. If the module needs tables, include migration stubs.
- ℹ️ **views** — INFO. Optional. Not needed for API-only modules.
- ⚠️ **composer.json** — WARN. Adding a composer.json inside the module helps with IDEs and tests, though nWidart doesn't require it.

## Install & Smoke‑Test (nWidart Laravel)

1. Copy the module folder into `Modules/Subcontractor` (final name must match `module.json` "name").
2. Run: `php artisan module:list` — confirm **Subcontractor** appears and is **Enabled**.
3. Publish assets (if applicable): `php artisan module:publish Subcontractor`.
4. Run migrations (if present): `php artisan module:migrate Subcontractor`.
5. Check routes: `php artisan route:list | grep Subcontractor` then visit `/subcontractor` or whatever the route file defines.
6. Watch the logs: `storage/logs/laravel.log` for class/namespace errors.

### Worksuite Compatibility (Heads‑up)
Worksuite SaaS does **not** natively load nWidart modules. To port this:
- Wrap controllers/views into a Worksuite Module skeleton (ServiceProvider, `ModuleServiceProvider.php`, `module.json` in Worksuite format).
- Replace `route('...')` helpers with Worksuite's route names & middlewares.
- Move migrations to Worksuite's migration runner and respect table prefixes.
- Rewire auth/tenancy hooks to Worksuite guards/policies.
If you want, I can generate a Worksuite‑ready skeleton and map file paths for a P1 port.

### Common Pitfalls
- Folder name mismatch with `module.json` "name" → module won’t register.
- Namespace drift (e.g., `Modules\Subcontractor\` vs code using `Modules\Example\`) → class not found.
- Missing `Routes/web.php` registration in provider → 404.
- Blade view namespace not published → `view not found`.
