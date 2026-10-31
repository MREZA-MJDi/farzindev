<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Public indexing is enabled by default. Administrators can still enable
     * any noindex/nofollow safeguard explicitly from Admin > SEO Settings.
     */
    public function up(): void
    {
        DB::table('storefront_settings')
            ->whereIn('key', [
                'seo_development_mode',
                'seo_robots_noindex',
                'seo_robots_nofollow',
                'seo_robots_noarchive',
                'seo_robots_nosnippet',
                'seo_robots_noimageindex',
            ])
            ->update(['value' => '0']);

        Cache::forget('storefront.seo-settings.v1');
        Cache::forget('storefront.sitemap.xml.v1');
    }

    public function down(): void
    {
        // Do not re-enable noindex on rollback: preserving a site's current
        // SEO choice is safer than silently hiding it from search engines.
    }
};
