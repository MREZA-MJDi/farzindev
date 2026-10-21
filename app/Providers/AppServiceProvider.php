<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
    }
}
