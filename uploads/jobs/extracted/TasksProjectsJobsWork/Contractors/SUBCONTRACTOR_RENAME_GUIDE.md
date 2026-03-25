# Subcontractor Rename Guide (WMS → Subcontractors)

Use these non-destructive replacements (review diffs before committing):

## Blade views (Resources/views/**/*.blade.php)
- Replace display strings only:
  - 'Writer' → 'Subcontractor'
  - 'Writers' → 'Subcontractors'
  - 'Article' → 'Job'
  - 'Articles' → 'Jobs'

Example (Linux/macOS):
```bash
grep -RIl --include="*.blade.php" -e "Writer\|Article" Resources/views | xargs -I{} sed -i.bak   -e "s/\bWriters\b/Subcontractors/g"   -e "s/\bWriter\b/Subcontractor/g"   -e "s/\bArticles\b/Jobs/g"   -e "s/\bArticle\b/Job/g" {}
```

## Route aliases
Add the routes in `Routes/web.php` from `Routes/web_subcontractor_patch.md` (keeps original routes working).

## Language
New file: `Resources/lang/en/subcontractor.php` (already created).

## Database
Run the migration we created (renaming writers/articles tables to subcontractors/jobs).

## Controllers/Models (optional deeper refactor)
- If you want full code-level rename later:
  - `Entities/Writer.php` → `Entities/Subcontractor.php` (adjust namespace & table)
  - `Entities/Article.php` → `Entities/Job.php`
  - Update controller imports: `use Modules\Article\Entities\Writer` → `...\Subcontractor`
  - Update `$table` names in models if they’re hardcoded.
  - Update route names `admin.article.*` → `admin.subcontractor.*` (after adding aliases).
