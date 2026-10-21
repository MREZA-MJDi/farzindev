<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\JalaliHelper;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Keep every calendar period in this report anchored to one timestamp.
        $reportingDate = now();

        /*
        |--------------------------------------------------------------------------
        | Global Statistics
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::query()->count();

        $activeProducts = Product::query()
            ->where('status', 'active')
            ->count();

        // Variant-backed products track inventory on their active options;
        // the parent product's stock column is only authoritative when there
        // are no active variants.
        $lowStockProducts = Product::query()
            ->where(function ($query): void {
                $query
                    ->where(function ($baseStock): void {
                        $baseStock
                            ->whereDoesntHave('variants', fn ($variantQuery) => $variantQuery
                                ->where('is_active', true))
                            ->whereBetween('stock', [1, 5]);
                    })
                    ->orWhereHas('variants', fn ($variantQuery) => $variantQuery
                        ->where('is_active', true)
                        ->whereBetween('stock', [1, 5]));
            })
            ->count();

        $outOfStockProducts = Product::query()
            ->where(function ($query): void {
                $query
                    ->where(function ($baseStock): void {
                        $baseStock
                            ->whereDoesntHave('variants', fn ($variantQuery) => $variantQuery
                                ->where('is_active', true))
                            ->where('stock', '<=', 0);
                    })
                    ->orWhere(function ($variantStock): void {
                        $variantStock
                            ->whereHas('variants', fn ($variantQuery) => $variantQuery
                                ->where('is_active', true))
                            ->whereDoesntHave('variants', fn ($variantQuery) => $variantQuery
                                ->where('is_active', true)
                                ->where('stock', '>', 0));
                    });
            })
            ->count();

        $totalCustomers = User::query()
            ->where('role', 'customer')
            ->count();

        $totalOrders = Order::query()->count();

        $pendingOrders = Order::query()
            ->whereIn('status', [
                'pending',
                'processing',
            ])
            ->count();

        $inventoryIssueOrders = Order::query()
            ->whereNotNull('inventory_issue_at')
            ->where('payment_status', 'paid')
            ->count();

        $paidOrders = Order::query()
            ->where('payment_status', 'paid')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Revenue
        |--------------------------------------------------------------------------
        |
        | Only successfully paid orders are counted as revenue.
        |
        */

        $totalRevenue = Order::query()
            ->where('payment_status', 'paid')
            ->sum('total');

        [$currentMonthStart, $nextMonthStart] = JalaliHelper::monthRange($reportingDate);
        [$previousMonthStart, $currentMonthStartForPrevious] = JalaliHelper::monthRange($reportingDate, 1);

        $currentMonthRevenue = Order::query()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $currentMonthStart)
            ->where('created_at', '<', $nextMonthStart)
            ->sum('total');

        $currentMonthOrders = Order::query()
            ->where('created_at', '>=', $currentMonthStart)
            ->where('created_at', '<', $nextMonthStart)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Previous Month Comparison
        |--------------------------------------------------------------------------
        */

        $previousMonthRevenue = Order::query()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $previousMonthStart)
            ->where('created_at', '<', $currentMonthStartForPrevious)
            ->sum('total');

        $revenueGrowthPercent = 0;

        if ((float) $previousMonthRevenue > 0) {
            $revenueGrowthPercent = round(
                (
                    (
                        (float) $currentMonthRevenue
                        - (float) $previousMonthRevenue
                    )
                    / (float) $previousMonthRevenue
                ) * 100,
                1
            );
        } elseif ((float) $currentMonthRevenue > 0) {
            $revenueGrowthPercent = 100;
        }

        /*
        |--------------------------------------------------------------------------
        | Six Month Revenue Chart
        |--------------------------------------------------------------------------
        */

        $monthlyRevenue = $this->getMonthlyRevenue($reportingDate);

        /*
        |--------------------------------------------------------------------------
        | Order Status Distribution
        |--------------------------------------------------------------------------
        */

        $orderStatus = [
            'pending' => Order::query()
                ->where('status', 'pending')
                ->count(),

            'processing' => Order::query()
                ->where('status', 'processing')
                ->count(),

            'shipped' => Order::query()
                ->where('status', 'shipped')
                ->count(),

            'delivered' => Order::query()
                ->where('status', 'delivered')
                ->count(),

            'cancelled' => Order::query()
                ->where('status', 'cancelled')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Top Products
        |--------------------------------------------------------------------------
        */

        $topProducts = $this->getTopProducts();

        /*
        |--------------------------------------------------------------------------
        | Installment Orders
        |--------------------------------------------------------------------------
        */

        $installmentOrders = Order::query()
            ->where('payment_method', 'installment')
            ->count();

        $installmentPaidOrders = Order::query()
            ->where('payment_method', 'installment')
            ->where('payment_status', 'paid')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Orders
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::query()
            ->with([
                'user',
            ])
            ->latest()
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Customers
        |--------------------------------------------------------------------------
        */

        $recentCustomers = User::query()
            ->where('role', 'customer')
            ->latest()
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Product Inventory
        |--------------------------------------------------------------------------
        */

        $featuredProductsCount = Product::query()
            ->where('is_featured', true)
            ->count();

        $newProductsCount = Product::query()
            ->where('is_new', true)
            ->count();

        $installmentProductsCount = Product::query()
            ->where('installment_enabled', true)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Average Order Value
        |--------------------------------------------------------------------------
        */

        $averageOrderValue = Order::query()
            ->where('payment_status', 'paid')
            ->avg('total');

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('admin.dashboard.index', [
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,

            'totalCustomers' => $totalCustomers,

            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'paidOrders' => $paidOrders,
            'inventoryIssueOrders' => $inventoryIssueOrders,

            'totalRevenue' => $totalRevenue,
            'currentMonthRevenue' => $currentMonthRevenue,
            'currentMonthOrders' => $currentMonthOrders,
            'previousMonthRevenue' => $previousMonthRevenue,
            'revenueGrowthPercent' => $revenueGrowthPercent,
            'averageOrderValue' => $averageOrderValue,

            'monthlyRevenue' => $monthlyRevenue,

            'orderStatus' => $orderStatus,

            'topProducts' => $topProducts,

            'installmentOrders' => $installmentOrders,
            'installmentPaidOrders' => $installmentPaidOrders,

            'recentOrders' => $recentOrders,
            'recentCustomers' => $recentCustomers,

            'featuredProductsCount' => $featuredProductsCount,
            'newProductsCount' => $newProductsCount,
            'installmentProductsCount' => $installmentProductsCount,
        ]);
    }

    /**
     * Get revenue for the latest six months.
     */
    protected function getMonthlyRevenue(\DateTimeInterface $reportingDate): Collection
    {
        return Cache::remember(
            'admin.dashboard.monthly-revenue.' . $reportingDate->format('Y-m'),
            now()->addMinute(),
            function () use ($reportingDate): Collection {
                $months = collect();

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            [$monthStart, $nextMonthStart] = JalaliHelper::monthRange($reportingDate, $monthsAgo);

            $revenue = Order::query()
                ->where('payment_status', 'paid')
                ->where('created_at', '>=', $monthStart)
                ->where('created_at', '<', $nextMonthStart)
                ->sum('total');

            $orders = Order::query()
                ->where('payment_status', 'paid')
                ->where('created_at', '>=', $monthStart)
                ->where('created_at', '<', $nextMonthStart)
                ->count();

            $months->push([
                'key' => JalaliHelper::format($monthStart, 'Y-m'),
                'label' => JalaliHelper::format($monthStart, 'M'),
                'full_label' => JalaliHelper::format($monthStart, 'F Y'),
                'revenue' => (float) $revenue,
                'orders' => $orders,
            ]);
        }

                return $months;
            }
        );
    }

    /**
     * Get best selling products from paid orders.
     */
    protected function getTopProducts(): Collection
    {
        return Cache::remember(
            'admin.dashboard.top-products.v1',
            now()->addMinute(),
            fn (): Collection => OrderItem::query()
            ->with([
                'product.images.media',
            ])
            ->whereHas(
                'order',
                fn ($query) => $query->where(
                    'payment_status',
                    'paid'
                )
            )
            ->selectRaw(
                'product_id,
                 product_name,
                 SUM(quantity) as sold_quantity,
                 SUM(total) as revenue'
            )
            ->groupBy(
                'product_id',
                'product_name'
            )
            ->orderByDesc('sold_quantity')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name,
                    'quantity' => (int) $item->sold_quantity,
                    'revenue' => (float) $item->revenue,
                    'image' => $item->product?->images?->first()?->url,
                ];
            })
        );
    }
}
