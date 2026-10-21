# Farzin — Kitchen Hoods & Sinks

Farzin is a Persian RTL e-commerce storefront focused on kitchen range hoods and kitchen sinks. The project is built with Laravel and includes a product catalogue, category browsing, product variants and images, search/filtering, cart, customer accounts, order management, payment flows, and a dedicated admin dashboard. Payment and order workflows should be verified against the current implementation and configured gateway before production use.

## Technology
- PHP `^8.2`, Laravel `^12.0`
- Blade, Tailwind CSS 4, Alpine.js, Vite
- Eloquent models and database migrations
- Responsive Persian RTL storefront and administration dashboard

## Local setup
```bash
git clone https://github.com/MREZA-MJDi/farzindev.git
cd farzindev
composer install
```

Copy `.env.example` to `.env` (Windows CMD: `copy .env.example .env`), configure the database, and set a strong local `ADMIN_PASSWORD`. Never commit the real `.env` file or production credentials.

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`. For Vite hot reload, run `npm run dev` in a separate terminal.

## Catalogue and imagery
- Create real Farzin products, specifications, stock, images, and variants from the admin dashboard.
- `php artisan db:seed` creates the Farzin categories, the configured admin account, and 50 editable catalogue preview records (25 hoods and 25 sinks) without generated images.
- New catalogue records use preview names and prices; verify each specification, SKU, price, stock count and product photo in the admin dashboard before publishing. In production, new preview records are drafts with zero stock.
- The category seeder hides inherited demo furniture categories. The product seeder archives inherited products with the `LIV-*` SKU prefix, preserves existing order history, and does not overwrite already-existing seeded records that staff have edited.
- To upload product covers and gallery images, the dashboard allows JPG/JPEG/PNG/WEBP up to 20 MB. The PHP runtime must accept requests of at least 26 MB for these limits to work.
- The `/blog` page contains a starter set of buying guides for hoods, sinks, and installation measurements. Its copy is currently maintained in `resources/views/blog/index.blade.php`, not in an admin CMS.

## Deployment

The current deployment target is an Ubuntu VPS over SSH, not cPanel. Follow [the Ubuntu VPS deployment runbook](docs/deploy-vps-ubuntu.md) for server packages, Nginx, TLS, database permissions, safe releases, Laravel caches, uploads, scheduler, and production verification.

[The older Hostiran cPanel guide](docs/deploy-hostiran-cpanel.md) is retained only for reference and should not be used as the current deployment procedure.

The GitHub Actions workflow builds a `farzin-vps-release` artifact after the test suite, production asset build, seeder smoke tests and Laravel cache-command checks pass. A green artifact is not proof of live DNS/TLS, verified catalogue data, email delivery or payment-provider readiness. Keep the external gateways disabled until provider-specific merchant API requests and server-side callbacks pass sandbox verification.

## Tests
```bash
php artisan test
```

## Inventory reservations and scheduled tasks

Stock is reserved transactionally when an order is created. A confirmed payment commits that reservation; a failed payment or cancellation releases it. If a gateway confirms payment after an unpaid reservation has expired and the stock is no longer available, the order is still recorded as paid and an explicit inventory warning appears in the admin dashboard so the order can be reconciled.

Unpaid reservations expire after 24 hours. Enable the Laravel scheduler on the production server; otherwise stale unpaid reservations will need to be cancelled manually from the admin order page.

For a Linux VPS, add this cron entry (replace the path and PHP binary with the values for the server):

\`\`\`cron
* * * * * cd /path/to/farzindev && php artisan schedule:run >> /dev/null 2>&1
\`\`\`

For cPanel, add the same command under **Cron Jobs** using the absolute project path and the server's PHP CLI binary.

## Production safety
Use sandbox credentials during payment integration testing. Confirm payment status through the verified gateway callback/server-side flow rather than relying on a browser redirect. Back up the database before running seeders on an existing installation and review migration changes before deployment.
