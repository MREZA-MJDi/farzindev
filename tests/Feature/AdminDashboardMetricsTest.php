<?php

namespace Tests\Feature;

use App\Helpers\JalaliHelper;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_loads_only_bounded_chart_data_and_important_links(): void
    {
        Cache::flush();

        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Dashboard Admin',
        ]);

        $this->order('FAR-DASHBOARD-PAID', 'delivered', 'paid', 25000000, now());
        $this->order('FAR-DASHBOARD-PENDING', 'pending', 'pending', 5000000, now());

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('totalRevenue', 25000000.0)
            ->assertViewHas('monthlyRevenue', function ($months): bool {
                return $months->count() === 6
                    && $months->last()['key'] === JalaliHelper::format(now(), 'Y-m');
            })
            ->assertViewHas('orderStatus', function (array $statuses): bool {
                return $statuses['delivered'] === 1
                    && $statuses['pending'] === 1;
            })
            ->assertSee('روند فروش شش ماه اخیر')
            ->assertSee('وضعیت سفارش‌ها')
            ->assertSee(route('admin.products.index'), false)
            ->assertSee(route('admin.orders.index'), false)
            ->assertSee(route('admin.cms.index'), false);

        $viewData = $response->viewData('page');
        $this->assertArrayNotHasKey('totalProducts', $viewData);
        $this->assertArrayNotHasKey('recentOrders', $viewData);
        $this->assertArrayNotHasKey('recentCustomers', $viewData);
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
