# Production release and readiness checklist

This runbook covers updates to an existing Farzin deployment. It is intentionally conservative: it does not reset the live database, reseed the live catalogue, or overwrite uploaded files.

## Before releasing a new main commit

1. Confirm the GitHub Actions run for the exact commit is green. The workflow checks Laravel tests, the Vite build, route/view/config cache commands, and responsive browser smoke tests at mobile, tablet, and desktop widths.
2. Confirm the actual production product names, descriptions, prices, SKUs, stock, specifications, and images have been reviewed. The production seeder deliberately does not publish preview products.
3. Back up the production database and uploaded files before schema changes. Keep the backup outside the public web root and verify the backup file is non-empty.
4. On an existing Git checkout, inspect `git status --short` before pulling. Preserve or intentionally reconcile local changes; never use `git reset --hard` or `git clean -fd` as a routine deployment step.

## Updating an existing Git checkout

Run the commands from the application root, for example `/var/www/farzindev`:

```bash
cd /var/www/farzindev
git status --short
git fetch origin
git log --oneline HEAD..origin/main
git pull --ff-only origin main

composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Back up the live database before this step; review pending migrations first.
php artisan migrate:status
php artisan migrate --force

# Run this only when deploying from source and Node/npm are intentionally
# installed on the server. A CI release artifact should already contain assets.
npm ci
npm run build

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan farzin:preflight
```

If the server deploys a CI-built release artifact instead of Git source, do not overwrite the existing `.env` or uploaded files and do not rebuild assets unnecessarily; follow the artifact deployment process and run the migration/cache/preflight steps against the deployed release.

## Blog or sitemap returns HTTP 500 after a release

The public blog and sitemap query the `blog_posts` table. A code-only pull does not execute database migrations. Check the exact error and migration state first:

```bash
cd /var/www/farzindev
php artisan migrate:status
tail -n 120 storage/logs/laravel.log
```

If `2026_10_23_000001_create_blog_posts_table` is pending, back up the live database, review the migration, and then run:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan farzin:preflight
```

Do not run `migrate:fresh`, do not delete the migrations table, and do not reset the live database to resolve a missing table. If the migration is already recorded as applied, use the Laravel log to identify the actual exception instead of rerunning or rolling back migrations blindly.

## Native blog CMS checks

The blog is managed inside Laravel; no WordPress connection is used. Before publishing:
- Confirm each article has an original title, readable English slug, useful excerpt, substantive body, cover image with descriptive alt text, unique meta title/description, author, and correct publication date.
- Keep drafts and future-scheduled articles out of public listing and sitemap.
- Check that each published article has a working canonical URL and `BlogPosting` structured data, and that related/internal links do not point to drafts or 404 pages.
- The current editor stores article body as plain text. Treat advanced rich-text editing, categories/tags, author archive pages, and editorial workflow as separate future CMS enhancements; do not claim they exist unless implemented.

## Production gates that still require real operational data

- Keep `APP_DEBUG=false`, set `APP_URL` to the canonical HTTPS origin, and enable `SESSION_SECURE_COOKIE=true`.
- Check the preflight output for missing tables, pending migrations, a missing Vite manifest, an incorrect storage symlink, or development SEO mode.
- The site-wide pre-launch SEO lock is enabled by default. Public pages must emit `noindex, nofollow`; `robots.txt` must keep `Allow: /` so crawlers can read the meta directive; `sitemap.xml` must return 404 and must not be advertised in robots.txt. Never use `Disallow: /` as a substitute for the meta robots gate.
- Only the designated SEO owner should release indexing: review real product data and images, category descriptions, published blog content and publication dates, canonical HTTPS URLs, structured data, redirects, and page-level meta first. Then disable **قفل انتشار** in Admin → SEO Settings, explicitly enable sitemap generation, and verify that home/product/category/blog pages return the intended index directives and canonical URLs.
- External online payment integrations are deliberately fail-closed in the current code. Provider credentials and environment flags are not sufficient: finish the provider-specific create/verify and signed callback contracts, then validate successful, failed, duplicate, and delayed callbacks in the provider sandbox before enabling live charging.
- Verify SMTP delivery if password reset or outgoing email is enabled, and check that database backups and uploaded-media backups can actually be restored.

## Manual live smoke test

After deployment, verify the home page, shop, category list and detail, product detail, blog list and article, contact, login/register, cart, customer account, and admin flows. Check mobile widths (360–390 px), tablet (768–1023 px), and desktop (1024 px and wider). Confirm that no page returns HTTP 500, the header/footer remain available, the mobile bottom navigation is visible only below the desktop breakpoint, and no unexpected horizontal overflow appears.
