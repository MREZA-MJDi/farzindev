<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\StorefrontSetting;
use App\Models\User;
use App\Services\Seo\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StorefrontPaginationAndSeoManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_public_category_directory_is_paginated_and_keeps_only_twelve_categories_per_page(): void
    {
        for ($index = 1; $index <= 13; $index++) {
            Category::create([
                'name' => 'Pagination Category ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'slug' => 'pagination-category-' . $index,
                'description' => 'Category used for public pagination checks.',
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Pagination Category 01')
            ->assertSee('Pagination Category 12')
            ->assertDontSee('Pagination Category 13')
            ->assertSee('page=2', false);

        $this->get(route('categories.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Pagination Category 13')
            ->assertDontSee('Pagination Category 01');
    }

    public function test_account_address_history_is_paginated_without_losing_saved_addresses(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        for ($index = 1; $index <= 11; $index++) {
            Address::create([
                'user_id' => $customer->id,
                'title' => 'Saved Address ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone' => '09123456789',
                'province' => 'Tehran',
                'city' => 'Tehran',
                'address' => 'Test street ' . $index,
                'postal_code' => str_pad((string) $index, 10, '0', STR_PAD_LEFT),
                'unit' => null,
                'is_default' => $index === 1,
                'created_at' => now()->addSeconds($index),
                'updated_at' => now()->addSeconds($index),
            ]);
        }

        $this->actingAs($customer)
            ->get(route('account.addresses.index'))
            ->assertOk()
            ->assertSee('Saved Address 11')
            ->assertDontSee('Saved Address 01')
            ->assertSee('page=2', false);

        $this->actingAs($customer)
            ->get(route('account.addresses.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Saved Address 01');
    }

    public function test_admin_customer_details_paginate_order_history_but_keep_accurate_total_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        for ($index = 1; $index <= 11; $index++) {
            Order::create([
                'user_id' => $customer->id,
                'order_number' => 'FAR-CUSTOMER-HISTORY-' . str_pad((string) $index, 5, '0', STR_PAD_LEFT),
                'status' => 'delivered',
                'payment_status' => 'paid',
                'payment_method' => 'online',
                'payment_provider' => null,
                'subtotal' => 25000000,
                'shipping_cost' => 0,
                'discount' => 0,
                'total' => 25000000,
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone' => '09123456789',
                'email' => $customer->email,
                'province' => 'Tehran',
                'city' => 'Tehran',
                'address' => 'Customer address',
                'postal_code' => '1234567890',
                'created_at' => now()->subDays($index),
                'updated_at' => now()->subDays($index),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('11')
            ->assertSee('FAR-CUSTOMER-HISTORY-00001')
            ->assertDontSee('FAR-CUSTOMER-HISTORY-00011')
            ->assertSee('orders_page=2', false);

        $this->actingAs($admin)
            ->get(route('admin.customers.show', ['customer' => $customer->id, 'orders_page' => 2]))
            ->assertOk()
            ->assertSee('FAR-CUSTOMER-HISTORY-00011')
            ->assertDontSee('FAR-CUSTOMER-HISTORY-00001');
    }

    public function test_sitemap_segment_route_serves_bounded_xml_and_rejects_unknown_pages(): void
    {
        $this->get(route('sitemap.segment', ['type' => 'pages', 'page' => 1]))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee('<loc>' . app(SeoSettings::class)->canonicalUrl('/', false) . '</loc>', false);

        $this->get(route('sitemap.segment', ['type' => 'pages', 'page' => 2]))
            ->assertNotFound();

        $this->get('/sitemap/unknown/1.xml')
            ->assertNotFound();
    }

    public function test_sitemap_segment_route_serves_xml_and_rejects_unknown_pages(): void
    {
        $this->get(route('sitemap.segment', ['type' => 'pages', 'page' => 1]))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee('<loc>' . app(SeoSettings::class)->canonicalUrl('/', false) . '</loc>', false);

        $this->get(route('sitemap.segment', ['type' => 'pages', 'page' => 2]))
            ->assertNotFound();

        $this->get('/sitemap/unknown/1.xml')
            ->assertNotFound();
    }

    public function test_seo_dashboard_values_override_real_titles_and_descriptions_for_main_pages(): void
    {
        $settings = [
            'seo_home_title' => 'عنوان مدیریت‌شده خانه فرزین',
            'seo_home_description' => 'توضیحات مدیریت‌شده صفحه اصلی فرزین برای موتورهای جستجو.',
            'seo_shop_title' => 'عنوان مدیریت‌شده فروشگاه فرزین',
            'seo_shop_description' => 'توضیحات مدیریت‌شده برای صفحه فروشگاه فرزین.',
            'seo_categories_title' => 'عنوان مدیریت‌شده دسته‌بندی‌ها',
            'seo_categories_description' => 'توضیحات مدیریت‌شده برای فهرست دسته‌بندی‌ها.',
            'seo_blog_title' => 'عنوان مدیریت‌شده مجله فرزین',
            'seo_blog_description' => 'توضیحات مدیریت‌شده برای فهرست مقالات فرزین.',
        ];

        foreach ($settings as $key => $value) {
            StorefrontSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        app(SeoSettings::class)->clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<title>عنوان مدیریت‌شده خانه فرزین</title>', false)
            ->assertSee('name="description" content="توضیحات مدیریت‌شده صفحه اصلی فرزین برای موتورهای جستجو."', false);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('<title>عنوان مدیریت‌شده فروشگاه فرزین</title>', false)
            ->assertSee('name="description" content="توضیحات مدیریت‌شده برای صفحه فروشگاه فرزین."', false);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('<title>عنوان مدیریت‌شده دسته‌بندی‌ها</title>', false)
            ->assertSee('name="description" content="توضیحات مدیریت‌شده برای فهرست دسته‌بندی‌ها."', false);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('<title>عنوان مدیریت‌شده مجله فرزین</title>', false)
            ->assertSee('name="description" content="توضیحات مدیریت‌شده برای فهرست مقالات فرزین."', false);
    }

    public function test_shop_filter_category_dataset_remains_bounded_and_selected_category_stays_available(): void
    {
        for ($index = 1; $index <= 105; $index++) {
            Category::create([
                'name' => 'Shop Filter Category ' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'slug' => 'shop-filter-category-' . $index,
                'description' => 'Category used for bounded shop filters.',
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }

        $selected = Category::query()->where('slug', 'shop-filter-category-105')->firstOrFail();

        $this->get(route('shop.index', ['category' => $selected->slug]))
            ->assertOk()
            ->assertSee($selected->name)
            ->assertSee($selected->slug);
    }

    public function test_seo_admin_dashboard_exposes_page_specific_fields_and_live_preview_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.seo-settings.edit'))
            ->assertOk()
            ->assertSee('سئوی اختصاصی صفحه‌های اصلی')
            ->assertSee('seo_home_title', false)
            ->assertSee('seo_shop_title', false)
            ->assertSee('seo_categories_title', false)
            ->assertSee('seo_blog_title', false)
            ->assertSee('data-seo-page-preview', false)
            ->assertSee('data-seo-preview-title-output', false);
    }
}
