<?php

namespace Tests\Feature;

use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Services\Catalog\ProductAttributeManager;
use Database\Seeders\ProductAttributeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAttributeSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_dictionary_seeder_is_idempotent_and_creates_the_gas_category(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $attributeCount = ProductAttribute::query()->count();
        $optionCount = AttributeOption::query()->count();

        $this->seed(ProductAttributeSeeder::class);

        $this->assertSame($attributeCount, ProductAttribute::query()->count());
        $this->assertSame($optionCount, AttributeOption::query()->count());
        $this->assertDatabaseHas('categories', ['slug' => 'built-in-stove', 'name' => 'اجاق گاز صفحه‌ای']);
        $this->assertFalse(Category::query()->visibleInStorefront()->where('slug', 'built-in-stove')->exists());
        $hood = Category::query()->where('slug', 'hoods')->firstOrFail();
        $this->assertSame(3, $hood->attributes()->wherePivot('is_key_spec', true)->count());
        $this->assertTrue((bool) $hood->attributes()->where('key', 'hood_body_material')->firstOrFail()->pivot->is_filterable);
    }

    public function test_required_attributes_are_validated_by_type_and_category(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $sink = Category::query()->where('slug', 'sinks')->firstOrFail();
        $manager = app(ProductAttributeManager::class);

        $this->assertNotEmpty($manager->validateValues($sink->id, [], true));
        $this->assertSame([], $manager->validateValues($sink->id, [], false), 'An incomplete draft must remain saveable.');

        $value = function (string $key, string $type, string $text) {
            $attribute = ProductAttribute::query()->where('key', $key)->firstOrFail();
            if ($type === 'select') {
                $option = $attribute->options()->where('label', $text)->firstOrFail();
                return [$attribute->id => ['option_id' => $option->id]];
            }
            return [$attribute->id => ['value' => $text]];
        };

        $payload = array_replace(
            $value('installation', 'select', 'توکار'),
            $value('length', 'number', '86'),
            $value('width', 'number', '50'),
            $value('basin_count', 'number', '2'),
            $value('basin_depth', 'number', '20'),
            $value('sink_material', 'select', 'استیل')
        );

        $this->assertSame([], $manager->validateValues($sink->id, $payload, true));

        $hoodType = ProductAttribute::query()->where('key', 'hood_type')->firstOrFail();
        $payload[$hoodType->id] = ['option_id' => $hoodType->options()->firstOrFail()->id];
        $errors = $manager->validateValues($sink->id, $payload, false);
        $this->assertArrayHasKey('attribute_values.' . $hoodType->id, $errors);

        $payload[ProductAttribute::query()->where('key', 'width')->value('id')] = ['value' => 'wide'];
        $errors = $manager->validateValues($sink->id, $payload, false);
        $this->assertStringContainsString('باید عددی باشد', $errors['attribute_values.' . ProductAttribute::query()->where('key', 'width')->value('id') . '.value']);
    }

    public function test_typed_values_are_persisted_and_required_completeness_is_calculated(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $sink = Category::query()->where('slug', 'sinks')->firstOrFail();
        $manager = app(ProductAttributeManager::class);
        $product = Product::query()->create([
            'category_id' => $sink->id,
            'name' => 'سینک تست مشخصات',
            'slug' => 'typed-sink-test',
            'sku' => 'FAR-TYPED-SINK-001',
            'price' => 10000000,
            'stock' => 4,
            'status' => 'draft',
        ]);

        $values = [];
        foreach ([
            'installation' => ['option_id' => ProductAttribute::where('key', 'installation')->firstOrFail()->options()->where('label', 'توکار')->value('id')],
            'length' => ['value' => '86'],
            'width' => ['value' => '50'],
            'basin_count' => ['value' => '2'],
            'basin_depth' => ['value' => '20'],
            'sink_material' => ['option_id' => ProductAttribute::where('key', 'sink_material')->firstOrFail()->options()->where('label', 'استیل')->value('id')],
        ] as $key => $input) {
            $values[ProductAttribute::query()->where('key', $key)->value('id')] = $input;
        }

        $manager->sync($product, $values);

        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $product->id,
            'attribute_id' => ProductAttribute::query()->where('key', 'width')->value('id'),
            'value_number' => 50,
        ]);
        $this->assertSame(
            ['complete' => 6, 'required' => 6, 'is_complete' => true],
            $manager->completeness($product)
        );
    }

    public function test_storefront_attribute_filter_only_returns_products_matching_selected_option(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $sink = Category::query()->where('slug', 'sinks')->firstOrFail();
        $material = ProductAttribute::query()->where('key', 'sink_material')->firstOrFail();
        $steel = $material->options()->where('label', 'استیل')->firstOrFail();
        $granite = $material->options()->where('label', 'گرانیت')->firstOrFail();

        foreach ([['استیل', $steel], ['گرانیت', $granite]] as $index => [$label, $option]) {
            $product = Product::query()->create([
                'category_id' => $sink->id,
                'name' => 'سینک ' . $label,
                'slug' => 'sink-filter-' . $index,
                'sku' => 'FAR-SINK-FILTER-' . $index,
                'price' => 10000000 + $index,
                'stock' => 3,
                'status' => 'active',
            ]);
            ProductAttributeValue::query()->create([
                'product_id' => $product->id,
                'attribute_id' => $material->id,
                'option_id' => $option->id,
            ]);
            ProductImage::query()->create([
                'product_id' => $product->id,
                'path' => 'https://cdn.example.test/sink-filter-' . $index . '.webp',
                'alt' => $product->name,
                'sort_order' => 0,
                'is_primary' => true,
            ]);
        }

        $this->get(route('shop.index', [
            'category' => $sink->slug,
            'attributes' => [$material->id => $steel->id],
        ]))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 1
                && $products->getCollection()->pluck('name')->all() === ['سینک استیل']);
    }
    public function test_category_faqs_render_from_database_with_faqpage_schema(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $category = Category::query()->where('slug', 'sinks')->firstOrFail();
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'سینک فعال برای نمایش پرسش‌ها',
            'slug' => 'sink-faq-visible',
            'sku' => 'FAR-SINK-FAQ-VISIBLE',
            'price' => 10000000,
            'stock' => 2,
            'status' => 'active',
        ]);
        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => 'https://cdn.example.test/sink-faq-visible.webp',
            'alt' => $product->name,
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('پرسش‌های متداول')
            ->assertSee('FAQPage', false)
            ->assertSee('ابعاد مناسب سینک را چطور انتخاب کنم؟');
    }

    public function test_category_page_filters_products_by_filterable_attribute(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $category = Category::query()->where('slug', 'sinks')->firstOrFail();
        $attribute = ProductAttribute::query()->where('key', 'sink_material')->firstOrFail();
        $steel = $attribute->options()->where('label', 'استیل')->firstOrFail();
        $granite = $attribute->options()->where('label', 'گرانیت')->firstOrFail();

        foreach ([['سینک استیل دسته', $steel], ['سینک گرانیت دسته', $granite]] as $index => [$name, $option]) {
            $product = Product::query()->create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => 'category-filter-' . $index,
                'sku' => 'FAR-CATEGORY-FILTER-' . $index,
                'price' => 12000000,
                'stock' => 2,
                'status' => 'active',
            ]);
            ProductAttributeValue::query()->create([
                'product_id' => $product->id,
                'attribute_id' => $attribute->id,
                'option_id' => $option->id,
            ]);
            ProductImage::query()->create([
                'product_id' => $product->id,
                'path' => 'https://cdn.example.test/category-filter-' . $index . '.webp',
                'alt' => $name,
                'sort_order' => 0,
                'is_primary' => true,
            ]);
        }

        $this->get(route('categories.show', [
            'slug' => $category->slug,
            'attributes' => [$attribute->id => $steel->id],
        ]))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 1
                && $products->getCollection()->pluck('name')->all() === ['سینک استیل دسته']);
    }

    public function test_legacy_specification_migration_supports_dry_run_and_preserves_source_json(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $sink = Category::query()->where('slug', 'sinks')->firstOrFail();
        $legacy = [
            ['label' => 'نحوه نصب', 'value' => 'توکار'],
            ['label' => 'طول', 'value' => '۸۶'],
        ];
        $product = Product::query()->create([
            'category_id' => $sink->id,
            'name' => 'سینک قدیمی برای مهاجرت',
            'slug' => 'legacy-sink-migration',
            'sku' => 'FAR-LEGACY-MIGRATION',
            'price' => 9000000,
            'stock' => 1,
            'status' => 'draft',
            'specifications' => $legacy,
        ]);

        $this->artisan('catalog:migrate-legacy-specifications', ['--dry-run' => true])->assertExitCode(0);
        $this->assertDatabaseMissing('product_attribute_values', ['product_id' => $product->id]);

        $this->artisan('catalog:migrate-legacy-specifications')->assertExitCode(0);
        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $product->id,
            'attribute_id' => ProductAttribute::query()->where('key', 'length')->value('id'),
            'value_number' => 86,
        ]);
        $this->assertSame($legacy, $product->fresh()->specifications);
    }

    public function test_legacy_migration_skips_suspicious_width_for_double_bowl_sink(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $sink = Category::query()->where('slug', 'sinks')->firstOrFail();
        $product = Product::query()->create([
            'category_id' => $sink->id,
            'name' => 'سینک نیازمند بررسی ابعاد',
            'slug' => 'legacy-sink-suspicious-width',
            'sku' => 'FAR-LEGACY-SUSPICIOUS',
            'price' => 9000000,
            'stock' => 1,
            'status' => 'draft',
            'specifications' => [
                ['label' => 'تعداد لگن', 'value' => '۲'],
                ['label' => 'عرض', 'value' => '۵۰'],
            ],
        ]);

        $this->artisan('catalog:migrate-legacy-specifications')->assertExitCode(0);
        $this->assertDatabaseMissing('product_attribute_values', [
            'product_id' => $product->id,
            'attribute_id' => ProductAttribute::query()->where('key', 'width')->value('id'),
        ]);
    }

    public function test_shop_defaults_to_in_stock_products_and_can_sort_unavailable_items_last(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $category = Category::query()->where('slug', 'sinks')->firstOrFail();
        foreach ([['سینک موجود', 4], ['سینک ناموجود', 0]] as $index => [$name, $stock]) {
            $product = Product::query()->create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => 'stock-sort-' . $index,
                'sku' => 'FAR-STOCK-SORT-' . $index,
                'price' => 11000000 + $index,
                'stock' => $stock,
                'status' => 'active',
            ]);
            ProductImage::query()->create([
                'product_id' => $product->id,
                'path' => 'https://cdn.example.test/stock-sort-' . $index . '.webp',
                'alt' => $name,
                'sort_order' => 0,
                'is_primary' => true,
            ]);
        }

        $this->get(route('shop.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee('سینک موجود')
            ->assertDontSee('سینک ناموجود');

        $this->get(route('shop.index', ['category' => $category->slug, 'in_stock' => 0, 'sort' => 'available_first']))
            ->assertOk()
            ->assertSeeInOrder(['سینک موجود', 'سینک ناموجود']);
    }

    public function test_gas_category_is_hidden_from_public_until_an_active_product_exists(): void
    {
        $this->seed(ProductAttributeSeeder::class);
        $category = Category::query()->where('slug', 'built-in-stove')->firstOrFail();

        $this->get(route('categories.index'))->assertDontSee($category->slug);
        $this->get(route('categories.show', $category->slug))->assertNotFound();
        $this->get(route('shop.index'))->assertDontSee($category->slug);
    }

}
