# P3 — Contractor Management

- Renamed module to **Contractor** (`Modules\Contractor`), URLs `/contractor*`, route names `contractor.*`.
- Table now **contractors**; migration will rename from `subcontractors` if present. **SoftDeletes** added.
- Activity log: `contractor_activity_logs` with auto-logging on create/update/delete.
- Company scoping: `company_id` auto-filled from `auth()->user()->company_id` if available.
- Policies: use `user->can('contractor.manage')` when available; otherwise permissive fallback.
- API: `Routes/api.php` under middleware `['api','auth']` at `/contractors/*`.

Upgrade:
```
php artisan cache:clear && php artisan config:clear && php artisan route:clear
php artisan migrate
```
