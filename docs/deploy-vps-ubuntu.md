# Deploy Farzin on an Ubuntu VPS

This guide targets an Ubuntu LTS VPS accessed over SSH. The Nginx document root must be the Laravel `public/` directory; never expose the repository root.

## Production gates

Before accepting real orders, all of these must be true:

- The release branch's GitHub Actions workflow is green, including tests, asset build, seeder smoke test, and Laravel config/route/view cache commands.
- The production database has been backed up and pending migrations reviewed.
- The actual shipping fee and optional free-shipping threshold have been entered in **Admin → Storefront Settings → Shipping**; the default is zero and means shipping is free until configured.
- The `ADMIN_EMAIL` and `ADMIN_PASSWORD` values are unique, intentional one-time bootstrap credentials. The initial admin must be able to sign in before the password is removed from the environment and the config cache rebuilt.
- The official Farzin product data, prices, specifications, SKUs, stock and real images have replaced or verified all preview catalogue records. Seeder preview products are not a verified live catalogue.
- A real sandbox payment has been completed and its server-side callback verified. The DigiPay, SnapPay and TorobPay adapters are configurable generic HTTP adapters; their payload fields, authentication, endpoint paths, success codes, callback signatures and amount units must be confirmed against the official merchant API contract. They are disabled by default. Each concrete adapter must override `isVerificationSuccessful()` to map the provider's documented success response. The generic base class deliberately rejects HTTP 2xx responses without an explicit positive verification flag and rejects payment redirect URLs that are not HTTPS. Do not enable a provider based solely on the CI mocks.
- SMTP credentials are configured if password-reset or other outgoing mail is enabled. The example environment uses a log mailer and is not a production mail setup.

## 1. Prepare the VPS

Use a supported Ubuntu LTS release and PHP 8.3 (the version exercised in CI). Install Nginx, MySQL, PHP-FPM/CLI and the extensions used by Laravel:

```bash
sudo apt update
sudo apt install -y nginx mysql-server unzip git curl   php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring   php8.3-xml php8.3-curl php8.3-gd php8.3-zip php8.3-bcmath
```

Install Composer 2 from its official installer and verify `php -v`, `composer --version`, `nginx -v`, and `mysql --version`. If the selected Ubuntu release does not provide PHP 8.3 packages from its configured repositories, configure a trusted PHP package source or choose a matching Ubuntu LTS image; do not silently run an older PHP version.

Create a dedicated database and least-privilege user in MySQL:

```sql
CREATE DATABASE farzin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'farzin_app'@'localhost' IDENTIFIED BY 'REPLACE_WITH_A_UNIQUE_SECRET';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES
  ON farzin.* TO 'farzin_app'@'localhost';
FLUSH PRIVILEGES;
```

Store the real database password only in the server's private `.env` file. Do not paste credentials into GitHub, chat, or a committed script.

## 2. Upload the release

Wait for the relevant GitHub Actions run to finish successfully, then download its **`farzin-vps-release`** artifact. Upload the archive to the VPS over SSH/SCP, for example:

```bash
scp farzin-vps-release.zip deploy@YOUR_SERVER:/tmp/
```

Create the application directory and extract it. Use your own non-root deployment account for file ownership:

```bash
sudo mkdir -p /var/www/farzin
sudo unzip -q /tmp/farzin-vps-release.zip -d /var/www/farzin
sudo chown -R deploy:www-data /var/www/farzin
sudo find /var/www/farzin -type d -exec chmod 0750 {} \;
sudo find /var/www/farzin -type f -exec chmod 0640 {} \;
sudo install -d -o deploy -g www-data -m 2770 \
  /var/www/farzin/storage/app/public \
  /var/www/farzin/storage/framework/cache/data \
  /var/www/farzin/storage/framework/sessions \
  /var/www/farzin/storage/framework/views \
  /var/www/farzin/storage/logs \
  /var/www/farzin/bootstrap/cache
sudo chown -R deploy:www-data /var/www/farzin/storage /var/www/farzin/bootstrap/cache
sudo chmod -R ug+rwX /var/www/farzin/storage /var/www/farzin/bootstrap/cache
sudo chmod -R g+s /var/www/farzin/storage /var/www/farzin/bootstrap/cache
```

If this is a later release, preserve the existing `.env` and uploaded files under `storage/app/public`; do not blindly extract a new archive over them. Back up the database and uploads before changing the existing release.

## 3. Configure the environment

Create `.env` only on a fresh installation:

```bash
cd /var/www/farzin
cp .env.example .env
nano .env
```

Set at least these values using the real domain and database credentials:

```dotenv
APP_NAME=Farzin
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR_DOMAIN
APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=farzin
DB_USERNAME=farzin_app
DB_PASSWORD=REPLACE_WITH_THE_DATABASE_SECRET

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=YOUR_SMTP_HOST
MAIL_PORT=587
MAIL_USERNAME=YOUR_SMTP_USERNAME
MAIL_PASSWORD=YOUR_SMTP_PASSWORD
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS=YOUR_VERIFIED_SENDER
MAIL_FROM_NAME="Farzin"
```

Keep the existing `APP_KEY` after the first generation. Do not regenerate it on normal releases. Keep `APP_DEBUG=false` on a public server. The `.env.example` lists each gateway's supported configuration keys, but the current adapters do not automatically exchange `client_id`/`client_secret` for provider tokens. Configure the provider's documented authentication flow in code before production use, and do not enable a gateway until its server-side credentials, API endpoints, callback URL, verification response and amount unit have been verified with that provider.

Set `ADMIN_NAME`, `ADMIN_EMAIL` and a unique `ADMIN_PASSWORD` only if this is a fresh database that still needs the initial administrator seeded.

## 4. Install, migrate and seed carefully

The release artifact includes Composer's production dependencies and compiled Vite assets; these commands verify/rebuild dependencies for the target PHP runtime and prepare Laravel:

```bash
cd /var/www/farzin
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
[ -n "$(grep '^APP_KEY=' .env | cut -d '=' -f2-)" ] || php artisan key:generate
php artisan migrate --force
php artisan storage:link
```

Run `php artisan key:generate` only when `APP_KEY` is blank for a new installation. For a database that already has real data, back it up first and run migrations only after reviewing them.

On a **new, empty** database only, run `php artisan db:seed --force` after setting `ADMIN_PASSWORD`. The seeder creates the initial administrator and draft/zero-stock preview records; it is not a replacement for importing the verified commercial catalogue. Do not repeatedly seed a live catalogue without reviewing the seeder behavior. After the initial admin has been created, remove `ADMIN_PASSWORD` from `.env` and rerun `php artisan config:cache` so the one-time credential is also removed from the cached configuration.

Set upload limits in the PHP-FPM configuration to at least `upload_max_filesize=26M` and `post_max_size=28M` to support the application's 20 MB image limit. Reload PHP-FPM after changing these settings.

## 5. Configure Nginx

Create `/etc/nginx/sites-available/farzin` and replace the hostname and PHP-FPM socket with the values on the VPS:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name YOUR_DOMAIN www.YOUR_DOMAIN;
    root /var/www/farzin/public;
    index index.php;

    charset utf-8;
    client_max_body_size 28m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and validate the configuration:

```bash
sudo ln -s /etc/nginx/sites-available/farzin /etc/nginx/sites-enabled/farzin
sudo nginx -t
sudo systemctl reload nginx
```

After DNS points to the VPS, issue a TLS certificate using your chosen ACME client (for example, Certbot with its Nginx integration). Then ensure `APP_URL` is the HTTPS canonical domain and `SESSION_SECURE_COOKIE=true`.

## 6. Cache and scheduler

After environment values, migrations and the initial seeding are complete:

```bash
cd /var/www/farzin
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan about
```

Verify that PHP-FPM can write to `storage/` and `bootstrap/cache/`. If a cache command fails, do not continue to live traffic until the error is resolved. The CI workflow checks these cache commands, but server permissions and environment values still need verification.

The unpaid-inventory reservation expiry task must run every minute. Add this cron entry under the deployment/service account that can read the app and write its storage directories:

```cron
* * * * * cd /var/www/farzin && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

The app currently uses the synchronous queue in this recommended configuration. If you change `QUEUE_CONNECTION` to `database` or `redis`, provision and supervise a queue worker rather than assuming queued jobs run automatically.

## 7. Release verification

Before opening the site to customers, verify:

- HTTPS works and Nginx serves only `public/`; requesting `/.env` must not reveal application secrets.
- `php artisan migrate:status` shows all migrations applied.
- Home, shop, category, product, contact, login/register, cart, checkout, customer order history and admin flows behave correctly on desktop and mobile.
- A normal customer cannot access admin routes or another customer's cart, address or order.
- Uploads persist after a new release and render through `/storage/...`.
- Contact form submissions appear in the admin inbox; SMTP/password reset works if enabled.
- Test a real gateway in its sandbox and confirm the verified server callback changes local payment/order state exactly once before enabling real customer payments.
- Error logs show no recurring exceptions and no secret values; `APP_DEBUG` remains false.
- Database and uploaded media backups are configured and a restore procedure has been tested.

A successful CI run verifies code/tests/build and creates an archive; it does not verify DNS, TLS, firewall, server permissions, the live catalogue, SMTP delivery or a merchant's payment API.
