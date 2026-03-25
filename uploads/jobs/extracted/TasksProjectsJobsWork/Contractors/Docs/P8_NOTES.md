# P8 — Fit & Finish (MVP wrap)

- Added DB indexes for `contractors` (status, company_id, created_at) and reinforced indexes for tags/notes.
- Config files are now **publishable**: `php artisan vendor:publish --tag=contractor-config`
- Views include a unified alerts partial (flash + validation errors).
- Empty state copy improved.

**Upgrade**
```
php artisan cache:clear && php artisan config:clear && php artisan route:clear
php artisan migrate
php artisan vendor:publish --tag=contractor-config --force
```
