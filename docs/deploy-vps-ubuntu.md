# Deploy Farzin on an Ubuntu VPS

This guide targets an Ubuntu LTS VPS accessed over SSH. The Nginx document root must be the Laravel `public/` directory; never expose the repository root.

## Production gates

Before accepting real orders, all of these must be true:

- The release branch's GitHub Actions workflow is green, including tests, asset build, seeder smoke test, and Laravel config/route/view cache commands.
- The production database has been backed up and pending migrations reviewed.
- The actual shipping fee and optional free-shipping threshold have been entered in **Admin → Storefront Settings → Shipping**; the default is zero and means shipping is free until configured.
- The `ADMIN_EMAIL` and `ADMIN_PASSWORD` values are unique, intentional one-time bootstrap credentials. The initial admin must be able to sign in before the password is removed from the environment and the config cache rebuilt.
- Only verified Farzin product data, prices, specifications, SKUs, stock and real images are published. `DatabaseSeeder` deliberately skips `ProductSeeder` in production; local/staging preview records are not a verified live catalogue.
- **External online payment is intentionally unavailable in this code state.** `PaymentManager::LIVE_API_ADAPTERS` is empty because the current DigiPay, SnapPay and TorobPay classes still contain generic placeholder payloads. The three providers and Shaparak status page must remain in the "integration preparing" state until provider-specific authentication, create/verify payloads, signature rules, response success codes, amount units and redirect hosts have been implemented and reviewed. Setting environment flags alone cannot activate them. When the official merchant documents arrive, complete a sandbox transaction and verify its server callback before allowing real customer payments.
- SEO development protection defaults to `noindex,nofollow`. Before public launch, confirm the production canonical domain and TLS, then turn **Development mode** off in **Admin → SEO Settings** and verify `/robots.txt`, `/sitemap.xml` and the rendered page's canonical/robots tags.
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

Create a dedicated non-root deployment account once (replace `deploy` consistently if you use another account), and add it to the PHP-FPM group:

```bash
id deploy >/dev/null 2>&1 || sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG www-data deploy
```

Upload the successful GitHub Actions `farzin-vps-release` archive and extract it into the app directory:

```bash
scp farzin-vps-release.zip deploy@YOUR_SERVER:/tmp/
sudo mkdir -p /var/www/farzin
sudo unzip -q /tmp/farzin-vps-release.zip -d /var/www/farzin
sudo chown -R deploy:www-data /var/www/farzin
sudo find /var/www/farzin -type d -exec chmod 0750 {} \\;
sudo find /var/www/farzin -type f -exec chmod 0640 {} \\;
sudo bash /var/www/farzin/scripts/repair-vps-permissions.sh
```

For later releases, preserve the existing `.env` and uploaded files under `storage/app/public`; do not blindly extract over them. Back up the database and uploads before changing the existing release. Run the repair script after each extraction or root-owned Git update. It only operates inside this Farzin project, does not follow the `public/storage` symlink, makes source files readable by the FPM group, keeps `.env` at `0640`, and sets `storage/` plus `bootstrap/cache/` to `deploy:www-data` with setgid directories (`2770`) and group-writable files (`0660`).

## 3. Configure the environment

Create `.env` only on a fresh installation:

```bash
cd /var/www/farzin
sudo -u deploy cp .env.example .env
sudo -u deploy nano .env
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
SESSION_DOMAIN=null
SESSION_COOKIE=farzin_session
CACHE_STORE=database
CACHE_PREFIX=farzin_
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

The CI release artifact already contains Composer's production dependencies and compiled Vite assets. Keep dependency installation and frontend builds in CI; do not run Composer or npm as root on the live VPS. After configuring `.env`, repair permissions and use the deployment wrapper for all Artisan commands:

```bash
cd /var/www/farzin
# The CI release archive already contains production vendor/ and public/build/.
# Do not run Composer or npm builds as root on the live VPS.
sudo bash scripts/repair-vps-permissions.sh
[ -n "$(grep '^APP_KEY=' .env | cut -d '=' -f2-)" ] || bash scripts/artisan-as-deploy.sh key:generate
bash scripts/artisan-as-deploy.sh migrate --force
bash scripts/artisan-as-deploy.sh storage:link
```

Run `php artisan key:generate` only when `APP_KEY` is blank for a new installation. For a database that already has real data, back it up first and run migrations only after reviewing them.

On a **new, empty** production database only, run `bash scripts/artisan-as-deploy.sh db:seed --force` after setting `ADMIN_EMAIL` and a unique `ADMIN_PASSWORD`. In production the seeder creates the configured administrator and core categories; it intentionally does **not** run `ProductSeeder` or create preview products. Add verified commercial products through the admin or an approved import. After the initial admin has been created, remove `ADMIN_PASSWORD` from `.env` and rerun `bash scripts/artisan-as-deploy.sh config:cache` so the one-time credential is also removed from cached configuration.

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

After the first admin signs in, review **Admin → SEO Settings**: set the canonical base URL to the exact HTTPS production origin, turn **Development mode** off only when the site is ready for indexing, keep the sitemap enabled, and inspect the generated `robots.txt` and `sitemap.xml`. Development mode is enabled by default as a pre-launch safeguard and will keep pages out of search results until disabled.

## 6. Cache and scheduler

After environment values, migrations and the initial seeding are complete:

```bash
cd /var/www/farzin
bash scripts/artisan-as-deploy.sh optimize:clear
bash scripts/artisan-as-deploy.sh config:cache
bash scripts/artisan-as-deploy.sh route:cache
bash scripts/artisan-as-deploy.sh view:cache
bash scripts/artisan-as-deploy.sh about
```

Verify that PHP-FPM can write to `storage/` and `bootstrap/cache/`. If a cache command fails, do not continue to live traffic until the error is resolved. The CI workflow checks these cache commands, but server permissions and environment values still need verification.

Run production preflight through the deployment wrapper:

```bash
bash scripts/artisan-as-deploy.sh farzin:preflight
```

The preflight checks both Vite's manifest and Laravel's generated `bootstrap/cache/packages.php`, `services.php`, config, event and route manifests. It fails when an existing manifest is owner-only or the cache directory is not writable. This catches the common outage caused by running `php artisan` as root with a restrictive umask: Laravel creates `root:root 0700` cache files, then PHP-FPM cannot register providers and pages fail with misleading `Target class [request|view] does not exist` errors.

**Never run Artisan directly as root.** Use `bash scripts/artisan-as-deploy.sh <command>`; it switches to `deploy` when invoked by root and sets `umask 0007`, so new cache files inherit the `www-data` group and stay readable by PHP-FPM. The CI release already contains Composer's production dependencies and compiled frontend assets; install/build them in CI, not as root on the VPS.


Invoke Laravel's scheduler every minute with this cron entry under the deployment/service account that can read the app and write its storage directories. The scheduler currently runs `orders:expire-unpaid --minutes=1440` hourly, so stale reservations can remain until the next hourly run; the cron itself must still invoke `schedule:run` every minute:

```cron
* * * * * cd /var/www/farzin && /bin/bash scripts/artisan-as-deploy.sh schedule:run >> /dev/null 2>&1
```

Run the cron entry as the `deploy` account. The wrapper sets the safe umask and invokes Artisan as that account. The app currently uses the synchronous queue in this recommended configuration. If you change `QUEUE_CONNECTION` to `database` or `redis`, provision and supervise a queue worker rather than assuming queued jobs run automatically.

## Hosting several independent projects on this VPS

Do not point 3–4 unrelated domains at this same Laravel installation. Each project needs its own release directory and Nginx document root, `.env`, `APP_KEY`, database and least-privilege database user, uploaded media/storage, logs, cache namespace and session cookie. Set a unique `CACHE_PREFIX` (for example `farzin_`, `project_b_`) and unique `SESSION_COOKIE`; keep `SESSION_DOMAIN=null`/unset for host-only session cookies unless shared subdomain login is explicitly designed. Never share `APP_KEY` values between projects.

Create one Nginx server block and TLS certificate per domain. Each `root` must point only to that project's `public/` directory. Use a dedicated PHP-FPM pool per application when resources allow it, with a separate Unix user/socket and a bounded `pm.max_children`; measure peak worker memory before choosing the limit instead of copying one number across all apps. Give each project its own queue worker process and queue name only when it actually needs asynchronous jobs. Do not start always-on workers for four apps just by default on a small VPS.

Before deciding whether the VPS can host four applications, measure installed RAM and CPU, MySQL buffer pool size, Redis memory, PHP-FPM process RSS under concurrent requests, queue load, and p95 request duration. Leave enough memory for the database, Nginx, filesystem cache, the operating system and deployment spikes. If the combined peak working set does not fit with headroom, use separate servers or move the database rather than overcommitting FPM workers.

Farzin's Vite build is performed in CI and shipped in the release artifact. Do not run `npm install`, a Vite dev server, or an asset build for every request or on the VPS during ordinary deployments.
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
