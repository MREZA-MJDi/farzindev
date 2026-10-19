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
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`. For Vite hot reload, run `npm run dev` in a separate terminal.

## Catalogue and imagery
- Create real Farzin products, specifications, stock, images, and variants from the admin dashboard.
- The catalogue seeders no longer invent sample furniture products or prices.
- Running the category seeder activates the Farzin hood and sink categories and hides the old demo furniture categories.
- The product seeder archives the inherited demo products with the `LIV-*` SKU prefix. Existing order records are not deleted.
- The `/blog` page contains a starter set of buying guides for hoods, sinks, and installation measurements. Its copy is currently maintained in `resources/views/blog/index.blade.php`, not in an admin CMS.

## Tests
```bash
php artisan test
```

## Production safety
Use sandbox credentials during payment integration testing. Confirm payment status through the verified gateway callback/server-side flow rather than relying on a browser redirect. Back up the database before running seeders on an existing installation and review migration changes before deployment.
