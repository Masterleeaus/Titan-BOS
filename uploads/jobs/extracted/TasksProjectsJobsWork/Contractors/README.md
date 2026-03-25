

# Contractor Management (Rebranded)
This module consolidates prior Contractor/Subcontractor/Article providers into a single namespace:
`Modules\WorksuiteContractors`

## Install / Upgrade
1. Place folder as `Modules/WorksuiteContractors`
2. Clear caches:
```
php artisan cache:clear && php artisan config:clear && php artisan route:clear
```
3. Migrate:
```
php artisan migrate
```
4. Seed permissions:
```
php artisan db:seed --class=Modules\WorksuiteContractors\Database\Seeders\ContractorsPermissionSeeder
```

## Bridge to Work Orders
- Pivot table: `work_order_contractor_assignments`
- UI: `/contractors/assignments` (requires `contractors.view_assignments`)
- API: `GET/POST/DELETE /api/contractors/assignments` (auth + can:*)
- Permissions to grant:
  - `contractors.assign`
  - `contractors.view_assignments`


## Schedule & Route (day view)
- URL: `/contractors/schedule` (needs `contractors.view_assignments`)
- Filter by date; per-contractor grouping with simple conflict detection.
- Set `duration_minutes` on assignments to improve overlap checks (defaults to 60).
