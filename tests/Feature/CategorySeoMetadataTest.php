<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategorySeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_category_specific_seo_and_it_is_rendered_publicly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.categories.store'), [
                'name' => 'هود مخفی',
                'slug' => 'built-in-hoods',
                'description' => 'توضیح کوتاه برای داده‌های متا',
                'content_title' => 'راهنمای انتخاب هود مخفی',
                'content_body' => 'برای انتخاب هود به ابعاد و قدرت مکش توجه کنید.',
                'meta_title' => 'خرید هود مخفی آشپزخانه | فرزین',
                'meta_description' => 'مدل‌های هود مخفی فرزین را با مشخصات و قیمت بررسی کنید.',
                'is_active' => '1',
                'sort_order' => '3',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $category = Category::query()->where('slug', 'built-in-hoods')->firstOrFail();

        $this->assertSame('خرید هود مخفی آشپزخانه | فرزین', $category->meta_title);
        $this->assertSame('مدل‌های هود مخفی فرزین را با مشخصات و قیمت بررسی کنید.', $category->meta_description);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('<title>خرید هود مخفی آشپزخانه | فرزین</title>', false)
            ->assertSee('name="description" content="مدل‌های هود مخفی فرزین را با مشخصات و قیمت بررسی کنید."', false)
            ->assertSee('راهنمای انتخاب هود مخفی')
            ->assertSee('برای انتخاب هود به ابعاد و قدرت مکش توجه کنید.')
            ->assertDontSee('farzin-category-detail__description', false);
    }

    public function test_active_category_created_in_admin_remains_visible_in_production_without_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.categories.store'), [
                'name' => 'دسته فعال بدون محصول',
                'slug' => 'active-empty-category',
                'description' => 'این دسته پیش از انتشار اولین محصول باید صفحه عمومی داشته باشد.',
                'is_active' => '1',
                'sort_order' => '4',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $category = Category::query()
            ->where('slug', 'active-empty-category')
            ->firstOrFail();

        // Exercise production visibility rules, not the permissive test rules.
        $this->app['env'] = 'production';

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('دسته فعال بدون محصول')
            ->assertDontSee('href="' . route('categories.show', 'active-empty-category') . '"', false);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('دسته فعال بدون محصول');

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('دسته فعال بدون محصول')
            ->assertSee('محصولی در این دسته پیدا نشد');
    }

    public function test_homepage_category_card_uses_the_slug_route_parameter(): void
    {
        $category = Category::create([
            'name' => 'دسته لینک عمومی',
            'slug' => 'public-category-link',
            'description' => 'آزمون لینک صفحه دسته‌بندی',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول دسته لینک عمومی',
            'slug' => 'public-category-link-product',
            'sku' => 'FAR-PUBLIC-CATEGORY-LINK',
            'short_description' => 'محصول آزمون لینک دسته‌بندی',
            'description' => 'محصول فعال برای نمایش کارت دسته‌بندی در صفحه اصلی.',
            'price' => 1000000,
            'stock' => 5,
            'status' => 'active',
            'is_featured' => false,
            'is_new' => false,
            'show_in_home_gallery' => true,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'path' => 'https://cdn.example.test/public-category-link.webp',
            'alt' => $product->name,
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="' . route('categories.show', 'public-category-link') . '"', false)
            ->assertDontSee('href="' . url('/categories/1') . '"', false);
    }

    public function test_admin_can_create_persian_category_without_manual_slug_and_it_is_public(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.categories.store'), [
                'name' => 'دسته‌بندی جدید فارسی',
                'description' => 'این دسته بدون وارد کردن دستی نشانی ساخته می‌شود.',
                'is_active' => '1',
                'sort_order' => '5',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $category = Category::query()
            ->where('name', 'دسته‌بندی جدید فارسی')
            ->firstOrFail();

        $this->assertMatchesRegularExpression('/^[a-z0-9][a-z0-9-]*$/', $category->slug);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('دسته‌بندی جدید فارسی');
    }

    public function test_category_slug_updates_create_permanent_redirects(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'دسته قدیمی',
            'slug' => 'old-category-slug',
            'description' => 'دسته آزمایشی',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.categories.update', $category), [
                'name' => 'دسته جدید',
                'slug' => 'new-category-slug',
                'description' => 'دسته با نشانی جدید',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $this->get('/categories/old-category-slug')
            ->assertRedirect('/categories/new-category-slug')
            ->assertStatus(301);

        $this->get('/categories/new-category-slug')
            ->assertOk()
            ->assertSee('دسته جدید');
    }

    public function test_admin_can_manage_category_guide_content_and_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'هود آشپزخانه',
            'slug' => 'kitchen-hoods',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Storage::fake('public');
        $image = \Illuminate\Http\UploadedFile::fake()->image('hood-guide.jpg', 1200, 675);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.categories.update', $category), [
                'name' => $category->name,
                'slug' => $category->slug,
                'content_title' => 'راهنمای خرید هود',
                'content_body' => 'پیش از انتخاب، عرض اجاق و مسیر خروج هوا را بررسی کنید.',
                'content_image' => $image,
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $category->refresh();

        $this->assertSame('راهنمای خرید هود', $category->content_title);
        $this->assertSame('پیش از انتخاب، عرض اجاق و مسیر خروج هوا را بررسی کنید.', $category->content_body);
        $this->assertNotEmpty($category->content_image);
        $this->assertNotNull($category->content_image_url);
        Storage::disk('public')->assertExists($category->content_image);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('راهنمای خرید هود')
            ->assertSee('پیش از انتخاب، عرض اجاق و مسیر خروج هوا را بررسی کنید.')
            ->assertSee($category->content_image_url, false);
    }

    public function test_category_image_forms_use_the_shared_preview_and_crop_editor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'دسته تست کراپ',
            'slug' => 'category-crop-test',
            'description' => 'آزمایش ویرایش تصویر دسته‌بندی',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        foreach ([
            route('admin.categories.create'),
            route('admin.categories.edit', $category),
        ] as $url) {
            $this->actingAs($admin, 'admin')
                ->get($url)
                ->assertOk()
                ->assertSee('name="content_title"', false)
                ->assertSee('name="content_body"', false)
                ->assertSee('name="content_image"', false)
                ->assertSee('data-image-editor-input', false)
                ->assertSee('data-image-editor-existing-preview', false)
                ->assertSee('data-image-editor-placeholder', false)
                ->assertSee('data-image-editor-panel', false)
                ->assertSee('data-image-editor-canvas', false)
                ->assertSee('data-image-editor-ratio', false)
                ->assertSee('data-image-editor-zoom', false)
                ->assertSee('data-image-editor-rotate', false);
        }
    }

}
