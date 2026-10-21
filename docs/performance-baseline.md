# Performance and production baseline

This project keeps performance work focused on measurable query and asset costs without changing checkout, inventory, payment, or storefront behavior.

## Runtime assets

- The shared application entry owns Alpine.js and shared storefront/admin behavior.
- The product-image editor is dynamically imported only on pages containing the image-editor control.
- Storefront GSAP motion is an optional chunk and is not loaded in the admin or the dedicated home page entry.
- The dedicated home entry remains isolated in `resources/js/home.js` and `resources/css/home.css`.

Build and verify the production asset graph:

```bash
npm ci
npm run build
```

## Database query paths

The performance-index migration adds composite indexes for the queries that commonly filter and order catalogue, product-image, variant-stock, and order records. It replaces the two narrower product indexes that are now covered by more useful composite indexes.

Deploy the migration during the normal release window after taking the project's usual database backup:

```bash
php artisan migrate --force
```

Adding indexes improves read paths at the cost of some additional index storage and write work. Review production query plans and table sizes before adding further indexes. Substring searches using leading wildcards (for example, `LIKE '%term%'`) generally do not benefit from ordinary B-tree indexes; changing search semantics requires a separate, database-specific design.

## Release verification

Run the same baseline checks as CI before deployment:

```bash
php artisan test
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

After confirming the deployed application is healthy, clear/rebuild caches only as prescribed by the VPS deployment runbook. A successful build or CI run is not a substitute for measuring response times and query plans on production-sized data.
