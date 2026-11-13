<?php

namespace App\Providers;

use App\Models\StorefrontSetting;
use App\Services\StorefrontPageContent;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keep default Laravel pagination calls consistent across admin and storefront.
        Paginator::defaultView('vendor.pagination.farzin');
        Paginator::defaultSimpleView('vendor.pagination.farzin-simple');

        // Share the cached public brand and page CMS payload only with top-level
        // public views and the layout. A wildcard composer would repeat cache
        // lookups and filesystem checks for every nested Blade component.
        View::composer(['layouts.app', 'home.index', 'about.index', 'legal.terms', 'legal.privacy', 'pages.policy', 'errors.*', 'auth.*', 'admin.auth.*'], function ($view): void {
            $settings = rescue(fn (): array => Cache::remember(
                'storefront.public-brand-settings.v1',
                now()->addMinutes(10),
                static fn (): array => StorefrontSetting::query()
                    ->whereIn('key', [
                        'brand_name', 'brand_logo', 'eitaa_url', 'bale_url',
                        'warranty_text', 'shipping_text', 'installation_text', 'returns_text',
                        'floating_contact_enabled',
                    ])
                    ->pluck('value', 'key')
                    ->all()
            ), [], report: false);

            $view->with('storefrontPublicSettings', $settings);
            $pageContent = rescue(
                fn (): array => app(StorefrontPageContent::class)->all(),
                [],
                report: false
            );
            $view->with('storefrontPageContent', $pageContent);
            $configuredBrandName = trim((string) ($settings['brand_name'] ?? ''));
            if (
                $configuredBrandName === ''
                || in_array(mb_strtolower($configuredBrandName), ['فرزین', 'فرزین فرجی', 'فرزین‌فرجی', 'farzin', 'farzin home', 'farzindev'], true)
            ) {
                $configuredBrandName = 'فرجی‌هوم';
            }
            $view->with('storefrontBrandName', $configuredBrandName);
            $brandLogoPath = $settings['brand_logo'] ?? null;
            $view->with('storefrontBrandLogoUrl', rescue(
                fn () => filled($brandLogoPath) && Storage::disk('public')->exists($brandLogoPath)
                    ? Storage::disk('public')->url($brandLogoPath)
                    : null,
                null,
                report: false
            ));
        });
    }
}
