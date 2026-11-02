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

        View::composer('*', function ($view): void {
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
            $view->with('storefrontBrandName', filled($settings['brand_name'] ?? null) ? $settings['brand_name'] : 'فرزین');
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
