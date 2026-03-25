# CRMCore — Extraction-Only nWidart Wrapper

This module **does not change business logic**. It simply packages your donor code under
`Modules/CRMCore/Donor/` and wires it in with a small ServiceProvider:

- Registers a PSR-4 autoload shim for donor namespaces
- Requires donor `routes/web.php` and `routes/api.php` if present
- Loads donor migrations and views
- Merges donor config files

When ready to fully convert, we can relocate Controllers/Models/Views into module-native
paths and update namespaces. This wrapper lets you run and test without rewriting code.
