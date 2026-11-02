<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_and_legacy_products_are_not_publicly_active(): void
    {
        $category = Category::query()->create([
            'name' => 'دسته واقعی',
            'slug' => 'real-category',
            'description' => 'دسته‌بندی آزمایشی آزمون',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        foreach (['REAL-001', 'FZ-SEED-HOOD-01', 'LIV-OLD-001'] as $sku) {
            Product::query()->create([
                'category_id' => $category->id,
                'name' => $sku,
                'slug' => strtolower($sku),
                'sku' => $sku,
                'short_description' => 'توضیح کوتاه آزمون',
                'description' => 'توضیحات کامل آزمون',
                'price' => 100000,
                'compare_at_price' => null,
                'stock' => 5,
                'meta_title' => null,
                'meta_description' => null,
                'status' => 'active',
                'is_featured' => false,
                'is_new' => false,
            ]);
        }

        $this->assertSame(
            ['REAL-001'],
            Product::query()->active()->pluck('sku')->all()
        );
    }

    public function test_empty_and_retired_demo_categories_are_not_publicly_active(): void
    {
        $this->app['env'] = 'staging';

        $empty = Category::query()->create([
            'name' => 'دسته خالی',
            'slug' => 'empty-category',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $legacy = Category::query()->create([
            'name' => 'مبلمان قدیمی',
            'slug' => 'furniture',
            'description' => 'دسته‌بندی قدیمی آزمون',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $real = Category::query()->create([
            'name' => 'هود آشپزخانه',
            'slug' => 'hoods',
            'description' => 'دسته‌بندی واقعی آزمون',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        foreach ([$legacy, $real] as $index => $category) {
            Product::query()->create([
                'category_id' => $category->id,
                'name' => 'محصول ' . $index,
                'slug' => 'product-' . $index,
                'sku' => 'SKU-' . $index,
                'short_description' => 'توضیح کوتاه آزمون',
                'description' => 'توضیحات کامل آزمون',
                'price' => 100000,
                'compare_at_price' => null,
                'stock' => 5,
                'meta_title' => null,
                'meta_description' => null,
                'status' => 'active',
                'is_featured' => false,
                'is_new' => false,
            ]);
        }

        $this->assertFalse(Category::query()->active()->whereKey($empty)->exists());
        $this->assertFalse(Category::query()->active()->whereKey($legacy)->exists());
        $this->assertTrue(Category::query()->active()->whereKey($real)->exists());
    }
}
