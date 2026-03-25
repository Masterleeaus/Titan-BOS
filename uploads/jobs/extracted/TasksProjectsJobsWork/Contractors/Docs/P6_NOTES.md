# P6 — Config-Driven Permissions & Menu

- **Config/permissions.php**: map abilities to your Worksuite permission slugs; optional auto-seeding (Spatie).
- **Config/menu.php**: choose `driver` = `app_menu` or `container_menu` and adjust the sidebar item fields.
- Policy now reads slugs from config and checks `$user->can(...)` when available.

**Change these if your build uses different permission names**.
