<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\JalaliHelper;
use App\Models\Order;
use App\Models\BlogPost;
use App\Models\OrderItem;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Keep the landing dashboard focused on charts. Detail lists and inventory
     * metrics belong to their dedicated paginated management screens.
     */
    public function index()
    {
        $reportingDate = now();

        $statusCounts = Order::query()
            ->select('status')
            ->selectRaw('COUNT(*) AS aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $orderStatus = [
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'processing' => (int) ($statusCounts['processing'] ?? 0),
            'shipped' => (int) ($statusCounts['shipped'] ?? 0),
            'delivered' => (int) ($statusCounts['delivered'] ?? 0),
            'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
        ];

        $monthlyRevenue = $this->getMonthlyRevenue($reportingDate);
        $totalRevenue = (float) $monthlyRevenue->sum('revenue');

        return view('admin.dashboard.index', compact('monthlyRevenue', 'orderStatus', 'totalRevenue'));
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
                        $revenueKey = 'revenue_' . $index;
                        $ordersKey = 'orders_' . $index;

                        return $period + [
                            'revenue' => (float) ($aggregates->{$revenueKey} ?? 0),
                            'orders' => (int) ($aggregates->{$ordersKey} ?? 0),
                        ];
                    });
            }
        );
    }

}
