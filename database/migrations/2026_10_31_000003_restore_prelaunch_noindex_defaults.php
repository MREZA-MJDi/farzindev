<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Support\\Facades\\Cache;
use Illuminate\\Support\\Facades\\DB;

return new class extends Migration
{
    /**
     * Keep Farzin out of search results until an SEO administrator explicitly
     * disables the existing development-mode gate in Admin > SEO Settings.
     */
    public function up(): void
    {
        DB::table('storefront_settings')->updateOrInsert(
            ['key' => 'seo_development_mode'],
            [
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Cache::forget('storefront.seo-settings.v1');
        Cache::forget('storefront.sitemap.xml.v1');
    }

    public function down(): void
    {
        // Preserve the safety lock on rollback. Releasing a site into search
        // results must always be an explicit administrator action.
    }
};
