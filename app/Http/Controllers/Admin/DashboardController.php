<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\JalaliHelper;
use App\Models\Order;
use App\Models\BlogPost;
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

        $productMetrics = Product::query()
            ->selectRaw(
                'COUNT(*) AS total_products,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS active_products,
                 COALESCE(SUM(CASE WHEN is_featured = ? THEN 1 ELSE 0 END), 0) AS featured_products,
                 COALESCE(SUM(CASE WHEN is_new = ? THEN 1 ELSE 0 END), 0) AS new_products,
                 COALESCE(SUM(CASE WHEN installment_enabled = ? THEN 1 ELSE 0 END), 0) AS installment_products',
                ['active', true, true, true]
            )
            ->first();

        $totalProducts = (int) $productMetrics->total_products;
        $activeProducts = (int) $productMetrics->active_products;

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

        $blogMetrics = BlogPost::query()
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = ? AND published_at IS NOT NULL AND published_at <= ? THEN 1 ELSE 0 END), 0) AS published_posts,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS draft_posts,
                 COALESCE(SUM(CASE WHEN status = ? AND published_at > ? THEN 1 ELSE 0 END), 0) AS scheduled_posts',
                ['published', $reportingDate, 'draft', 'published', $reportingDate]
            )
            ->first();

        $publishedBlogPosts = (int) $blogMetrics->published_posts;
        $draftBlogPosts = (int) $blogMetrics->draft_posts;
        $scheduledBlogPosts = (int) $blogMetrics->scheduled_posts;

        [$currentMonthStart, $nextMonthStart] = JalaliHelper::monthRange($reportingDate);
        [$previousMonthStart, $currentMonthStartForPrevious] = JalaliHelper::monthRange($reportingDate, 1);

        // One conditional aggregate supplies order KPIs, status distribution and revenue summaries.
        $orderMetrics = Order::query()
            ->selectRaw(
                'COUNT(*) AS total_orders,
                 COALESCE(SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END), 0) AS pending_orders,
                 COALESCE(SUM(CASE WHEN inventory_issue_at IS NOT NULL AND payment_status = ? THEN 1 ELSE 0 END), 0) AS inventory_issue_orders,
                 COALESCE(SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END), 0) AS paid_orders,
                 COALESCE(SUM(CASE WHEN payment_status = ? THEN total ELSE 0 END), 0) AS total_revenue,
                 COALESCE(SUM(CASE WHEN payment_status = ? AND created_at >= ? AND created_at < ? THEN total ELSE 0 END), 0) AS current_month_revenue,
                 COALESCE(SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END), 0) AS current_month_orders,
                 COALESCE(SUM(CASE WHEN payment_status = ? AND created_at >= ? AND created_at < ? THEN total ELSE 0 END), 0) AS previous_month_revenue,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS status_pending,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS status_processing,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS status_shipped,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS status_delivered,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS status_cancelled,
                 COALESCE(SUM(CASE WHEN payment_method = ? THEN 1 ELSE 0 END), 0) AS installment_orders,
                 COALESCE(SUM(CASE WHEN payment_method = ? AND payment_status = ? THEN 1 ELSE 0 END), 0) AS installment_paid_orders,
                 AVG(CASE WHEN payment_status = ? THEN total ELSE NULL END) AS average_order_value',
                [
                    'pending', 'processing',
                    'paid',
                    'paid',
                    'paid',
                    'paid', $currentMonthStart, $nextMonthStart,
                    $currentMonthStart, $nextMonthStart,
                    'paid', $previousMonthStart, $currentMonthStartForPrevious,
                    'pending', 'processing', 'shipped', 'delivered', 'cancelled',
                    'installment',
                    'installment', 'paid',
                    'paid',
                ]
            )
            ->first();

        $totalOrders = (int) $orderMetrics->total_orders;
        $pendingOrders = (int) $orderMetrics->pending_orders;
        $inventoryIssueOrders = (int) $orderMetrics->inventory_issue_orders;
        $paidOrders = (int) $orderMetrics->paid_orders;
        $totalRevenue = (float) $orderMetrics->total_revenue;
        $currentMonthRevenue = (float) $orderMetrics->current_month_revenue;
        $currentMonthOrders = (int) $orderMetrics->current_month_orders;
        $previousMonthRevenue = (float) $orderMetrics->previous_month_revenue;
        $installmentOrders = (int) $orderMetrics->installment_orders;
        $installmentPaidOrders = (int) $orderMetrics->installment_paid_orders;
        $averageOrderValue = $orderMetrics->average_order_value === null
            ? null
            : (float) $orderMetrics->average_order_value;

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
            'pending' => (int) $orderMetrics->status_pending,
            'processing' => (int) $orderMetrics->status_processing,
            'shipped' => (int) $orderMetrics->status_shipped,
            'delivered' => (int) $orderMetrics->status_delivered,
            'cancelled' => (int) $orderMetrics->status_cancelled,
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

        $featuredProductsCount = (int) $productMetrics->featured_products;
        $newProductsCount = (int) $productMetrics->new_products;
        $installmentProductsCount = (int) $productMetrics->installment_products;

        /*
        |--------------------------------------------------------------------------
        | Average Order Value
        |--------------------------------------------------------------------------
        */



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
            'publishedBlogPosts' => $publishedBlogPosts,
            'draftBlogPosts' => $draftBlogPosts,
            'scheduledBlogPosts' => $scheduledBlogPosts,
        ]);
    }

    /**
     * Get revenue for the latest six months.
     */
    protected function getMonthlyRevenue(\DateTimeInterface $reportingDate): Collection
    {
        return Cache::remember(
            'admin.dashboard.monthly-revenue.' . JalaliHelper::format($reportingDate, 'Y-m'),
            now()->addMinute(),
            function () use ($reportingDate): Collection {
                $periods = [];
                $expressions = [];
                $bindings = [];
                $firstMonthStart = null;
                $lastMonthEnd = null;

                for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
                    [$monthStart, $nextMonthStart] = JalaliHelper::monthRange($reportingDate, $monthsAgo);
                    $index = count($periods);

                    $expressions[] = "COALESCE(SUM(CASE WHEN created_at >= ? AND created_at < ? THEN total ELSE 0 END), 0) AS revenue_{$index}";
                    $bindings[] = $monthStart;
                    $bindings[] = $nextMonthStart;

                    $expressions[] = "COALESCE(SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END), 0) AS orders_{$index}";
                    $bindings[] = $monthStart;
                    $bindings[] = $nextMonthStart;

                    $periods[] = [
                        'key' => JalaliHelper::format($monthStart, 'Y-m'),
                        'label' => JalaliHelper::format($monthStart, 'M'),
                        'full_label' => JalaliHelper::format($monthStart, 'F Y'),
                    ];

                    $firstMonthStart ??= $monthStart;
                    $lastMonthEnd = $nextMonthStart;
                }

                $aggregates = Order::query()
                    ->where('payment_status', 'paid')
                    ->where('created_at', '>=', $firstMonthStart)
                    ->where('created_at', '<', $lastMonthEnd)
                    ->selectRaw(implode(",\n", $expressions), $bindings)
                    ->first();

                return collect($periods)
                    ->values()
                    ->map(function (array $period, int $index) use ($aggregates): array {
                        return $period + [
                            'revenue' => (float) ($aggregates->{"revenue_{$index}"} ?? 0),
                            'orders' => (int) ($aggregates->{"orders_{$index}"} ?? 0),
                        ];
                    });
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
                    'product.images' => fn ($query) => $query
                        ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                        ->limit(1),
                    'product.images.media:id,path',
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
