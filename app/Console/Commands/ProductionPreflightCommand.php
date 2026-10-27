<?php

namespace App\Console\Commands;

use App\Services\Payments\PaymentManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ProductionPreflightCommand extends Command
{
    protected $signature = 'farzin:preflight';

    protected $description = 'Check deployment prerequisites without modifying production data.';

    public function handle(): int
    {
        $checks = [];
        $isProduction = app()->environment('production');

        $add = static function (string $status, string $name, string $detail) use (&$checks): void {
            $checks[] = compact('status', 'name', 'detail');
        };

        $phpSupported = PHP_VERSION_ID >= 80300;
        $add(
            $phpSupported ? 'OK' : 'WARN',
            'PHP runtime',
            'Running PHP ' . PHP_VERSION . '; PHP 8.3+ is the version exercised by CI.'
        );

        $hasAppKey = filled(config('app.key'));
        $add(
            $hasAppKey ? 'OK' : 'FAIL',
            'APP_KEY',
            $hasAppKey ? 'Application key is configured.' : 'Set APP_KEY before serving application traffic.'
        );

        $debugDisabled = config('app.debug') === false;
        $add(
            $debugDisabled ? 'OK' : ($isProduction ? 'FAIL' : 'WARN'),
            'APP_DEBUG',
            $debugDisabled ? 'Debug mode is disabled.' : 'Debug mode is enabled; disable it before production traffic.'
        );

        $appUrl = (string) config('app.url');
        $urlParts = parse_url($appUrl) ?: [];
        $httpsUrl = strtolower((string) ($urlParts['scheme'] ?? '')) === 'https'
            && filled($urlParts['host'] ?? null);
        $add(
            $httpsUrl ? 'OK' : ($isProduction ? 'FAIL' : 'WARN'),
            'APP_URL',
            $httpsUrl ? 'Application URL uses HTTPS.' : 'Set APP_URL to the canonical HTTPS origin before production.'
        );

        $secureSessionCookie = config('session.secure') === true;
        $add(
            $secureSessionCookie ? 'OK' : ($isProduction ? 'FAIL' : 'WARN'),
            'Session cookie security',
            $secureSessionCookie ? 'Secure session cookies are enabled.' : 'Set SESSION_SECURE_COOKIE=true for HTTPS production.'
        );

        $databaseConnected = false;

        try {
            DB::connection()->getPdo();
            $databaseConnected = true;
            $add('OK', 'Database connection', 'The configured database connection is available.');
        } catch (Throwable) {
            $add(
                'FAIL',
                'Database connection',
                'Could not connect. Verify the server-side database service and credentials, then check Laravel logs.'
            );
        }

        if ($databaseConnected) {
            foreach ([
                'users',
                'categories',
                'products',
                'product_images',
                'product_variants',
                'product_variant_images',
                'media',
                'carts',
                'cart_items',
                'wishlists',
                'orders',
                'order_items',
                'order_installments',
                'addresses',
                'payments',
                'contact_settings',
                'contact_messages',
                'blog_posts',
                'storefront_settings',
            ] as $table) {
                $exists = Schema::hasTable($table);
                $add(
                    $exists ? 'OK' : 'FAIL',
                    'Database table: ' . $table,
                    $exists ? 'Table exists.' : 'Table is missing; review and apply the matching migration.'
                );
            }

            foreach ([
                ['product_images', 'media_id', 'Product gallery must support shared Media Library assets.'],
                ['products', 'show_in_home_gallery', 'Homepage mosaic requires the admin-managed gallery selection column.'],
                ['contact_settings', 'latitude', 'Contact page map coordinates require the latitude column.'],
                ['contact_settings', 'longitude', 'Contact page map coordinates require the longitude column.'],
                ['orders', 'inventory_reserved_at', 'Checkout safety requires the inventory reservation lifecycle migration.'],
                ['orders', 'inventory_committed_at', 'Checkout safety requires the inventory commit marker.'],
                ['product_variants', 'color_hex', 'Variant color picker requires color_hex.'],
                ['product_variant_images', 'product_variant_id', 'Variant gallery pivot table must be migrated.'],
                ['product_variant_images', 'product_image_id', 'Variant gallery pivot table must link product images.'],
            ] as [$table, $column, $detail]) {
                $exists = Schema::hasTable($table) && Schema::hasColumn($table, $column);
                $add(
                    $exists ? 'OK' : 'FAIL',
                    'Database column: ' . $table . '.' . $column,
                    $exists ? 'Column exists.' : $detail . ' Apply and verify the matching migration before using product variants.'
                );
            }

            if (! Schema::hasTable('migrations')) {
                $add('FAIL', 'Migration repository', 'The migrations table is missing.');
            } else {
                try {
                    $ranMigrations = DB::table('migrations')
                        ->pluck('migration')
                        ->map(static fn ($migration): string => (string) $migration)
                        ->all();

                    $migrationFiles = collect(File::files(database_path('migrations')))
                        ->map(static fn ($file): string => pathinfo($file->getFilename(), PATHINFO_FILENAME))
                        ->all();

                    $pendingMigrations = array_values(array_diff($migrationFiles, $ranMigrations));
                    $pendingSummary = implode(', ', array_slice($pendingMigrations, 0, 5));

                    $add(
                        $pendingMigrations === [] ? 'OK' : 'FAIL',
                        'Pending migrations',
                        $pendingMigrations === []
                            ? 'All migration files are recorded as applied.'
                            : count($pendingMigrations) . ' pending migration(s): ' . $pendingSummary
                                . (count($pendingMigrations) > 5 ? ', ...' : '')
                    );
                } catch (Throwable) {
                    $add(
                        'FAIL',
                        'Pending migrations',
                        'Could not read migration status. Run php artisan migrate:status and inspect the database connection.'
                    );
                }
            }

            if (Schema::hasTable('storefront_settings')) {
                $developmentMode = DB::table('storefront_settings')
                    ->where('key', 'seo_development_mode')
                    ->value('value');

                $indexingReady = (string) $developmentMode === '0';
                $add(
                    $indexingReady ? 'OK' : 'WARN',
                    'SEO indexing mode',
                    $indexingReady
                        ? 'SEO development mode is disabled.'
                        : 'Development mode may emit noindex directives. Verify Admin > SEO Settings before requesting indexing.'
                );
            }
        }

        $manifestExists = is_file(public_path('build/manifest.json'));
        $add(
            $manifestExists ? 'OK' : 'FAIL',
            'Compiled frontend assets',
            $manifestExists ? 'Vite manifest exists.' : 'Missing public/build/manifest.json; build or deploy the compiled assets.'
        );

        $storageLinked = is_link(public_path('storage')) && file_exists(public_path('storage'));
        $add(
            $storageLinked ? 'OK' : ($isProduction ? 'FAIL' : 'WARN'),
            'Public storage link',
            $storageLinked ? 'public/storage points to the storage disk.' : 'Create or repair the public/storage symlink with php artisan storage:link.'
        );

        $paymentManager = app(PaymentManager::class);
        $readyProviders = array_values(array_filter(
            ['digipay', 'snappay', 'torobpay'],
            fn (string $gateway): bool => $paymentManager->isReady($gateway)
        ));

        $add(
            $readyProviders !== [] ? 'OK' : 'WARN',
            'External payment readiness',
            $readyProviders !== []
                ? 'Ready providers: ' . implode(', ', $readyProviders) . '. Complete sandbox and callback verification before live charging.'
                : 'No external online payment provider is enabled and verified. Do not advertise online payment as available.'
        );

        $this->table(
            ['Status', 'Check', 'Details'],
            array_map(
                static fn (array $check): array => [$check['status'], $check['name'], $check['detail']],
                $checks
            )
        );

        $failures = count(array_filter(
            $checks,
            static fn (array $check): bool => $check['status'] === 'FAIL'
        ));
        $warnings = count(array_filter(
            $checks,
            static fn (array $check): bool => $check['status'] === 'WARN'
        ));

        $this->newLine();
        $this->line('Critical blockers: ' . $failures . ' | Warnings: ' . $warnings);

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
