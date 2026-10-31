<?php

namespace Tests\Feature;

use App\Helpers\JalaliHelper;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_aggregates_product_order_and_blog_metrics_without_changing_their_meaning(): void
    {
        Cache::flush();

        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Dashboard Admin',
        ]);

        $category = Category::create([
            'name' => 'دسته داشبورد',
            'slug' => 'dashboard-metrics-category',
            'description' => 'دسته آزمایش شاخص‌های داشبورد',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'محصول شاخص داشبورد',
            'slug' => 'dashboard-metrics-product',
            'sku' => 'FAR-DASHBOARD-METRIC',
            'short_description' => 'محصول آزمایشی',
            'description' => 'محصول برای آزمون aggregateهای داشبورد',
            'price' => 25000000,
            'stock' => 10,
            'status' => 'active',
            'is_featured' => true,
            'is_new' => true,
            'installment_enabled' => true,
        ]);

        $this->order('FAR-DASHBOARD-PAID', 'delivered', 'paid', 25000000, now());
        $this->order('FAR-DASHBOARD-PENDING', 'pending', 'pending', 5000000, now());

        BlogPost::create([
            'title' => 'مقاله منتشرشده داشبورد',
            'slug' => 'dashboard-published-post',
            'excerpt' => 'مقاله منتشرشده',
            'body' => 'متن آزمایشی',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $admin->id,
        ]);

        BlogPost::create([
            'title' => 'پیش‌نویس داشبورد',
            'slug' => 'dashboard-draft-post',
            'excerpt' => 'پیش‌نویس',
            'body' => 'متن آزمایشی',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        BlogPost::create([
            'title' => 'مقاله زمان‌بندی‌شده داشبورد',
            'slug' => 'dashboard-scheduled-post',
            'excerpt' => 'مقاله زمان‌بندی‌شده',
            'body' => 'متن آزمایشی',
            'status' => 'published',
            'published_at' => now()->addDay(),
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $response
            ->assertViewHas('totalProducts', 1)
            ->assertViewHas('activeProducts', 1)
            ->assertViewHas('featuredProductsCount', 1)
            ->assertViewHas('newProductsCount', 1)
            ->assertViewHas('installmentProductsCount', 1)
            ->assertViewHas('totalOrders', 2)
            ->assertViewHas('pendingOrders', 1)
            ->assertViewHas('paidOrders', 1)
            ->assertViewHas('currentMonthOrders', 2)
            ->assertViewHas('totalRevenue', 25000000.0)
            ->assertViewHas('currentMonthRevenue', 25000000.0)
            ->assertViewHas('averageOrderValue', 25000000.0)
            ->assertViewHas('installmentOrders', 0)
            ->assertViewHas('publishedBlogPosts', 4)
            ->assertViewHas('draftBlogPosts', 1)
            ->assertViewHas('scheduledBlogPosts', 1)
            ->assertViewHas('monthlyRevenue', function ($months): bool {
                return $months->count() === 6
                    && $months->last()['key'] === JalaliHelper::format(now(), 'Y-m');
            })
            ->assertViewHas('orderStatus', function (array $statuses): bool {
                return $statuses['delivered'] === 1
                    && $statuses['pending'] === 1;
            });
    }

    private function order(
        string $number,
        string $status,
        string $paymentStatus,
        float $total,
        $createdAt
    ): Order {
        return Order::create([
            'user_id' => null,
            'order_number' => $number,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => 'online',
            'payment_provider' => null,
            'subtotal' => $total,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => $total,
            'first_name' => 'Test',
            'last_name' => 'Buyer',
            'phone' => '09123456789',
            'email' => 'buyer@example.test',
            'province' => 'Tehran',
            'city' => 'Tehran',
            'address' => 'Test address',
            'postal_code' => '1234567890',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
