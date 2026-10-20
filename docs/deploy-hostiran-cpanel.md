# Deploy Farzin to Hostiran cPanel

This project is Laravel 12 and requires PHP 8.2 or newer. This checklist is for shared Linux hosting with cPanel; it is not a VPS/root-server deployment.

## Before changing DNS or files

1. In cPanel, confirm `farajihome.com` is listed under **Domains** and note its exact document root.
2. Confirm the hosting IP shown by Hostiran is the site's web IP. Do not use the separate "bank IP" for website DNS unless Hostiran explicitly says otherwise.
3. Back up any existing document-root files and any existing database before replacing or migrating data.
4. Change the hosting-panel password if it has been shared in chat. Do not put panel passwords, database passwords, APP_KEY or admin passwords in GitHub.

## cPanel checklist

- Select PHP **8.2+** in **MultiPHP Manager** or **Select PHP Version**.
- Enable at least: `ctype`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, and `session`.
- Create a MySQL database and a dedicated database user in **MySQL Databases**. Grant that user only the privileges required by the app.
- In **SSL/TLS Status**, issue/enable AutoSSL for `farajihome.com` and `www.farajihome.com` if available.
- If email already uses this domain, do not replace MX/TXT records when configuring the website's A records.

## Recommended file layout

**Best option:** keep the Laravel repository outside the public document root, and point the domain's document root to the repository's `public/` directory if cPanel allows it.

If the primary domain is fixed to `public_html`:

- Put the Laravel application in a private directory such as `/home/CPANEL_USER/farzin-app` (not inside `public_html`).
- Copy the *contents* of the project's `public/` folder into `public_html/`, including `.htaccess` and the prebuilt `build/` assets.
- Edit `public_html/index.php` so all three Laravel paths target the private app folder. If the directories are siblings (`public_html` and `farzin-app`), use:
  - `if (file_exists($maintenance = __DIR__.'/../farzin-app/storage/framework/maintenance.php')) { require $maintenance; }`
  - `require __DIR__.'/../farzin-app/vendor/autoload.php';`
  - `$app = require_once __DIR__.'/../farzin-app/bootstrap/app.php';`
  Replace `farzin-app` with the actual directory name. Do **not** copy `.env`, `vendor`, `storage`, `app`, or `database` into `public_html`.
- The public storage URL must also resolve from the public document root. If your host permits symlinks, create `public_html/storage` pointing to `../farzin-app/storage/app/public`. Otherwise ask Hostiran for its recommended Laravel public-storage setup before uploading product images.

## Build and install

Build frontend assets on a machine with Node.js before uploading, or use cPanel Terminal if Node/npm is supported:

```bash
npm ci
npm run build
```

Upload the project source and the generated `public/build/` directory. With cPanel Terminal/SSH available, from the private Laravel app directory run:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

Only run `cp .env.example .env` for a brand-new app. Never overwrite an existing production `.env`. Generate the APP_KEY once and retain it for future deploys.

Before `migrate --force`, create a database backup and review the pending migrations. The production seeders create the hood/sink categories, archive known old demo-furniture products with the `LIV-*` SKU prefix, create/update the admin from the environment, and create 50 **preview-only** hood/sink catalogue rows plus local SVG preview illustrations. In production those preview products are saved as drafts with zero stock; they are not verified Farzin models, specifications, or prices and must not be published to customers. Replace their data with the official catalogue and upload real product photography in the admin before publishing. The seeders do not create fictional customer orders or payments in production. Confirm the target database before running them.

If Terminal/SSH is unavailable, use cPanel File Manager or Git Version Control to upload source, but build `public/build` locally and upload Composer's `vendor/` directory from a machine using the same PHP-compatible dependency set. Do not leave deployment half-installed: the project must include `vendor/autoload.php` and `public/build/manifest.json`.

## Production .env

Set values privately in the actual server `.env`; never commit them:

```dotenv
APP_NAME="Farzin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://farajihome.com
APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=CPANEL_DATABASE_NAME
DB_USERNAME=CPANEL_DATABASE_USER
DB_PASSWORD=STRONG_DATABASE_PASSWORD

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

ADMIN_NAME="Farzin Admin"
ADMIN_EMAIL=admin@farajihome.com
ADMIN_PASSWORD=SET_A_UNIQUE_LONG_PASSWORD
```

Use the exact DB host shown by cPanel. cPanel database names and usernames are often prefixed with the hosting account name. Keep the actual APP_KEY generated by Artisan; do not regenerate it on later deployments. File-based session/cache and synchronous queues avoid requiring Redis or a queue worker on ordinary shared hosting.

After enabling HTTPS, test both `https://farajihome.com` and `https://www.farajihome.com`, and choose one canonical version. If you force www/non-www redirect, configure the canonical URL consistently.

## Release checks

- Verify homepage, shop, hood and sink categories, a product detail page, search/filter, contact form, registration, mobile login, cart, checkout, account pages, and admin permissions.
- Verify a customer cannot open admin URLs and cannot see another customer's carts, addresses, or orders.
- Run a sandbox payment end-to-end with the configured gateway and inspect the database status; CI mocks are not a substitute for a real gateway callback test.
- Review `storage/logs/laravel.log` after checks and ensure `APP_DEBUG=false`.
- Set up a daily database backup before accepting real customer orders.
