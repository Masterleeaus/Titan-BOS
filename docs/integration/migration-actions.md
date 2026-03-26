# WorkCore — Migration Actions

## ⚠ DO NOT RUN BLINDLY

WorkSuite migrations have been copied to `database/migrations/worksuite/` but
**NOT run**. One migration in particular is destructive if run against an existing
Titan-BOS database — see the critical risk section below.

## Critical risk: Non-SaaS → SaaS migration

```
database/migrations/worksuite/2024_01_12_214740_worksuite_non_saas_to_saas_migration.php
```

This migration:
- Adds `company_id` columns to ~50 existing tables (users, projects, tasks, etc.)
- Runs `NonSaasToSaasSeeder` which backfills data
- Is **idempotent** in WorkSuite but **NOT** safe against Titan-BOS schema

**Action:** **EXCLUDE** this file when running WorkSuite migrations. Move or delete it:
```bash
mv database/migrations/worksuite/2024_01_12_214740_worksuite_non_saas_to_saas_migration.php \
   database/migrations/worksuite/_EXCLUDED/
```

## Status: migrations not yet run

## CRM domain migrations (31 files)

These are in `database/migrations/worksuite/`. They create the following tables:

| Migration file pattern | Table(s) created | Risk |
|------------------------|-----------------|------|
| `*_create_leads_table*` | `leads` | Check — host has `crm_leads` (different schema) |
| `*_create_deals_table*` | `deals` | Check — host has `crm_deals` |
| `*_create_lead_pipelines*` | `lead_pipelines` | Low — no conflict |
| `*_create_client_details*` | `client_details` | Low |
| `*_create_contracts_table*` | `contracts` | Low |
| `*_create_estimate_requests*` | `estimate_requests` | Low |
| Other CRM support tables | various | Low |

## Tables that already exist in host (DO NOT re-create)

| Table | Host migration | Action |
|-------|---------------|--------|
| `users` | `2014_10_12_000000` | Skip — host owns this |
| `companies` | `2024_02_26_184945` | Skip — host owns this |
| `menus` | `2024_05_16_092520` | Skip — host owns this |
| `crm_leads` | `2026_03_25_*` | Skip — different table from WorkSuite `leads` |
| `crm_contacts` | `2026_03_25_*` | Skip |
| `crm_deals` | `2026_03_25_*` | Skip |
| `roles` / `permissions` | Spatie migration | Skip |

## Pending domain migrations (not yet extracted)

| Domain | Estimated tables |
|--------|-----------------|
| Finance / Sales | invoices, invoice_items, payments, expenses, estimates, credit_notes, taxes |
| HR / Attendance / Leave | employees, attendance, leaves, leave_types, shifts, departments, designations |
| Projects / Tasks / Time | projects, tasks, project_members, time_logs, milestones |
| Support / Comms | tickets, ticket_replies, notices, messages, message_settings |
| Platform / Misc | 50+ support tables |

## Full migration count (from deep scan)

WorkCore source contains **274 migrations** total across all domain slices:

| Category | Count |
|----------|-------|
| Core tables (users, companies, roles) | 15+ |
| Financial (invoices, payments, expenses) | 45+ |
| HR / Attendance / Leave | 50+ |
| Projects / Tasks / Timelogs | 40+ |
| Clients / CRM | 30+ |
| Tickets / Support | 20+ |
| Products / Orders | 15+ |
| Settings tables | 35+ |
| Custom fields | 10+ |
| Documents | 15+ |

Only the **31 CRM domain migrations** are currently in `database/migrations/worksuite/`.
Remaining 243 arrive when the other domain slices are extracted (Phase 2+).

## Recommended migration procedure (Phase 2)

1. Exclude the non-SaaS migration (see critical risk above).
2. Run `php artisan migrate:status` to see current host schema state.
3. For each WorkSuite migration, check if the target table already exists.
4. Prefix conflicting migration file names with `worksuite_` (e.g. `worksuite_leads`).
5. Run in domain order: CRM → Finance → Projects → HR → Support → Platform.

## Running CRM migrations (when ready)

```bash
php artisan migrate --path=database/migrations/worksuite
```

**Warning:** Review each file first. Do not run blindly.

## Column tenancy note

WorkSuite migrations use `company_id` as the tenant column. When creating tables, verify
whether to keep `company_id` (for future multi-company support) or add a `user_id` alias
column. The `config/worksuite.php` `tenancy.tenant_column` key controls this at runtime.
