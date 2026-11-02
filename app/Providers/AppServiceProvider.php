<?php

namespace App\Providers;

use App\Models\StorefrontSetting;
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
            $settings = Cache::remember(
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
            );

            $view->with('storefrontPublicSettings', $settings);
            $view->with('storefrontBrandName', $settings['brand_name'] ?? config('app.name', 'فرزین'));
            $view->with('storefrontBrandLogoUrl', filled($settings['brand_logo'] ?? null)
                ? Storage::disk('public')->url($settings['brand_logo'])
                : null);
        });
    }
}
