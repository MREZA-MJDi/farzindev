<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PreviewCatalogueCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_is_a_dry_run_by_default_and_apply_archives_without_deleting(): void
    {
        $hoods = Category::create([
            'name' => 'هود آشپزخانه',
            'slug' => 'hoods',
            'description' => 'دسته هود',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $tables = Category::create([
            'name' => 'میزها',
            'slug' => 'tables',
            'description' => 'دسته قدیمی',
            'is_active' => true,
            'sort_order' => 90,
        ]);

        $seedProduct = Product::create([
            'category_id' => $hoods->id,
            'name' => 'هود پیش‌نمایش',
            'slug' => 'farzin-hood-preview-01',
            'sku' => 'FZ-SEED-HOOD-01',
            'price' => 12500000,
            'stock' => 8,
            'status' => 'active',
            'is_featured' => true,
            'is_new' => true,
        ]);

        $shirt = Product::create([
            'category_id' => $hoods->id,
            'name' => 'تی‌شرت آزمایشی',
            'slug' => 'legacy-tshirt',
            'sku' => 'FAR-LEGACY-TEE-01',
            'price' => 500000,
            'stock' => 4,
            'status' => 'active',
        ]);

        $realProduct = Product::create([
            'category_id' => $hoods->id,
            'name' => 'هود واقعی فرزین',
            'slug' => 'farzin-real-hood',
            'sku' => 'FAR-REAL-HOOD-01',
            'price' => 21500000,
            'stock' => 5,
            'status' => 'active',
        ]);

        $this->assertSame(0, Artisan::call('farzin:cleanup-preview-catalogue'));
        $dryRun = Artisan::output();

        $this->assertStringContainsString('FZ-SEED-HOOD-01', $dryRun);
        $this->assertStringContainsString('تی‌شرت آزمایشی', $dryRun);
        $this->assertSame('active', $seedProduct->fresh()->status);
        $this->assertSame('active', $shirt->fresh()->status);
        $this->assertTrue($tables->fresh()->is_active);

        $this->assertSame(0, Artisan::call('farzin:cleanup-preview-catalogue', ['--apply' => true]));

        $this->assertSame('archived', $seedProduct->fresh()->status);
        $this->assertSame(0, (int) $seedProduct->fresh()->stock);
        $this->assertFalse($seedProduct->fresh()->is_featured);
        $this->assertFalse($seedProduct->fresh()->is_new);
        $this->assertSame('archived', $shirt->fresh()->status);
        $this->assertSame('active', $realProduct->fresh()->status);
        $this->assertFalse($tables->fresh()->is_active);

        // Cleanup preserves rows and any related order history.
        $this->assertDatabaseHas('products', ['id' => $seedProduct->id]);
        $this->assertDatabaseHas('products', ['id' => $shirt->id]);
        $this->assertDatabaseHas('products', ['id' => $realProduct->id]);
    }

    public function test_public_catalogue_scope_excludes_known_preview_and_unrelated_records(): void
    {
        $category = Category::create([
            'name' => 'هود آشپزخانه',
            'slug' => 'hoods',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'هود پیش‌نمایش',
            'slug' => 'hood-preview',
            'sku' => 'FZ-SEED-HOOD-01',
            'price' => 10000000,
            'stock' => 5,
            'status' => 'active',
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'تی‌شرت قدیمی',
            'slug' => 'old-shirt',
            'sku' => 'FAR-OLD-TEE',
            'price' => 100000,
            'stock' => 5,
            'status' => 'active',
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'هود فرزین H105',
            'slug' => 'hood-h105',
            'sku' => 'FAR-H105',
            'price' => 24000000,
            'stock' => 5,
            'status' => 'active',
        ]);

        $this->assertSame(
            ['FAR-H105'],
            Product::query()->publicCatalogue()->pluck('sku')->all()
        );
    }
}
