# WorkCore — Migration Actions

## Status

WorkSuite migrations have been copied to `database/migrations/worksuite/` but
**NOT run**. They must be reviewed against the current schema before execution.

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

## Recommended migration procedure (Phase 2)

1. Run `php artisan migrate:status` to see current state.
2. For each WorkSuite migration, check if the target table already exists.
3. Rename the migration file with a `worksuite_` prefix (e.g., `worksuite_leads`) if
   the table name conflicts with an existing host table.
4. Add `company_id` → `user_id` tenancy shim in migration if needed.
5. Run migrations in domain order: CRM → Finance → Projects → HR → Support → Platform.

## Running CRM migrations (when ready)

```bash
php artisan migrate --path=database/migrations/worksuite
```

**Warning:** Review each file first. Do not run blindly.

## Column tenancy note

WorkSuite migrations use `company_id` as the tenant column. When creating tables, verify
whether to keep `company_id` (for future multi-company support) or add a `user_id` alias
column. The `config/worksuite.php` `tenancy.tenant_column` key controls this at runtime.
