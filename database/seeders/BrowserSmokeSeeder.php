<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class BrowserSmokeSeeder extends Seeder
{
    /**
     * Browser-only catalogue fixtures. This seeder must never run in production
     * or be called by DatabaseSeeder; CI invokes it explicitly after the normal
     * empty-database seeder so public pages can be exercised without demo rows
     * leaking into an actual storefront database.
     */
    public function run(): void
    {
        if (App::environment('production')) {
            throw new \RuntimeException('BrowserSmokeSeeder is prohibited in production.');
        }

        $hoods = Category::firstOrCreate(
            ['slug' => 'hoods'],
            ['name' => 'هود آشپزخانه', 'description' => 'دسته تست مرورگر', 'is_active' => true, 'sort_order' => 1]
        );
        $sinks = Category::firstOrCreate(
            ['slug' => 'sinks'],
            ['name' => 'سینک ظرفشویی', 'description' => 'دسته تست مرورگر', 'is_active' => true, 'sort_order' => 2]
        );

        $fixtures = [
            ['category' => $hoods, 'slug' => 'farzin-hood-e2e-01', 'sku' => 'E2E-HOOD-01', 'name' => 'هود تست مرورگر ۷۰ سانتی‌متر', 'width' => 70, 'installation' => 'مخفی', 'price' => 12500000],
            ['category' => $hoods, 'slug' => 'farzin-hood-e2e-02', 'sku' => 'E2E-HOOD-02', 'name' => 'هود تست مرورگر ۹۰ سانتی‌متر', 'width' => 90, 'installation' => 'مورب', 'price' => 16500000],
            ['category' => $hoods, 'slug' => 'farzin-hood-e2e-03', 'sku' => 'E2E-HOOD-03', 'name' => 'هود تست مرورگر مدل سوم', 'width' => 70, 'installation' => 'مخفی', 'price' => 14500000],
            ['category' => $hoods, 'slug' => 'farzin-hood-e2e-04', 'sku' => 'E2E-HOOD-04', 'name' => 'هود تست مرورگر مدل چهارم', 'width' => 90, 'installation' => 'شومینه‌ای', 'price' => 18500000],
            ['category' => $sinks, 'slug' => 'farzin-sink-e2e-01', 'sku' => 'E2E-SINK-01', 'name' => 'سینک تست مرورگر ۱', 'width' => 80, 'installation' => 'روکار', 'price' => 8500000],
            ['category' => $sinks, 'slug' => 'farzin-sink-e2e-02', 'sku' => 'E2E-SINK-02', 'name' => 'سینک تست مرورگر ۲', 'width' => 90, 'installation' => 'توکار', 'price' => 9800000],
        ];

        foreach ($fixtures as $index => $fixture) {
            $product = Product::updateOrCreate(
                ['sku' => $fixture['sku']],
                [
                    'category_id' => $fixture['category']->id,
                    'name' => $fixture['name'],
                    'slug' => $fixture['slug'],
                    'short_description' => 'رکورد صرفاً برای تست خودکار رابط فروشگاه.',
                    'description' => 'این محصول داده واقعی فروشگاه نیست و فقط در دیتابیس تست مرورگر ساخته می‌شود.',
                    'price' => $fixture['price'],
                    'compare_at_price' => null,
                    'stock' => 5,
                    'width_cm' => $fixture['width'],
                    'installation_type' => $fixture['installation'],
                    'material' => $fixture['category']->slug === 'sinks' ? 'استیل تست' : 'فلز تست',
                    'suction_power_m3h' => $fixture['category']->slug === 'hoods' ? 650 : null,
                    'noise_db' => $fixture['category']->slug === 'hoods' ? 58 : null,
                    'power_watts' => $fixture['category']->slug === 'hoods' ? 180 : null,
                    'warranty_months' => null,
                    'status' => 'active',
                    'is_featured' => true,
                    'is_new' => $index === 0,
                    'show_in_home_gallery' => true,
                    'installment_enabled' => false,
                ]
            );

            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'path' => 'https://images.example.test/browser-smoke/' . $fixture['slug'] . '.webp'],
                [
                    'media_id' => null,
                    'alt' => $fixture['name'],
                    'sort_order' => 0,
                    'is_primary' => true,
                ]
            );
        }
    }
}
