<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class BrowserSmokeSeeder extends Seeder
{
    /**
     * Explicit, isolated browser fixtures. Never run this through the normal
     * application seeder and never allow it to write production catalogue data.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('BrowserSmokeSeeder is prohibited in production.');
        }

        $hoods = Category::query()->firstOrCreate(
            ['slug' => 'hoods'],
            [
                'name' => 'هود آشپزخانه',
                'description' => 'راهنمای انتخاب هود بر اساس نوع نصب، ابعاد و فضای آشپزخانه.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
        $sinks = Category::query()->firstOrCreate(
            ['slug' => 'sinks'],
            [
                'name' => 'سینک ظرفشویی',
                'description' => 'مدل‌های سینک را بر اساس جنس، تعداد لگن و روش نصب مقایسه کنید.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $fixtures = [
            [$hoods, 'E2E-HOOD-01', 'farzin-hood-e2e-01', 'هود تست مرورگر ۷۰ سانتی‌متر', 12500000],
            [$hoods, 'E2E-HOOD-02', 'farzin-hood-e2e-02', 'هود تست مرورگر ۹۰ سانتی‌متر', 16500000],
            [$hoods, 'E2E-HOOD-03', 'farzin-hood-e2e-03', 'هود تست مرورگر مدل سوم', 14500000],
            [$hoods, 'E2E-HOOD-04', 'farzin-hood-e2e-04', 'هود تست مرورگر مدل چهارم', 18500000],
            [$sinks, 'E2E-SINK-01', 'farzin-sink-e2e-01', 'سینک تست مرورگر ۱', 8500000],
            [$sinks, 'E2E-SINK-02', 'farzin-sink-e2e-02', 'سینک تست مرورگر ۲', 9800000],
        ];

        foreach ($fixtures as [$category, $sku, $slug, $name, $price]) {
            $product = Product::query()->updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => $slug,
                    'short_description' => 'اطلاعات محصول برای تست خودکار فروشگاه.',
                    'description' => 'این رکورد فقط در پایگاه داده‌ی تست مرورگر ایجاد می‌شود.',
                    'price' => $price,
                    'compare_at_price' => null,
                    'stock' => 5,
                    'status' => 'active',
                    'is_featured' => true,
                    'is_new' => false,
                    'show_in_home_gallery' => true,
                    'installment_enabled' => false,
                ]
            );

            ProductImage::query()->firstOrCreate(
                [
                    'product_id' => $product->id,
                    'path' => 'https://images.example.test/browser-smoke/' . $slug . '.webp',
                ],
                [
                    'media_id' => null,
                    'alt' => $name,
                    'sort_order' => 0,
                    'is_primary' => true,
                ]
            );
        }
    }
}
