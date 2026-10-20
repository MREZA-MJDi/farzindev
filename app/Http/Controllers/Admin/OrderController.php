<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\Inventory\InventoryReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $query = Order::query()
            ->with('user')
            ->latest();

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')
            );
        }

        if ($request->filled('payment_status')) {
            $query->where(
                'payment_status',
                $request->string('payment_status')
            );
        }

        if ($request->boolean('inventory_issue')) {
            $query->whereNotNull('inventory_issue_at');
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function ($query) use ($search) {
                $query->where(
                    'order_number',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'first_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'last_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'phone',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        $orders = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.orders.index',
            compact('orders')
        );
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $order->load([
            'user',
            'items.product',
            'items.variant',
            'latestPayment',
            'installments'
        ]);

        return view(
            'admin.orders.show',
            compact('order')
        );
    }

    /**
     * Update the order status.
     */
    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        InventoryReservationService $inventoryReservationService
    ): RedirectResponse {
        $status = $request->validated('status');

        $requiresRefundFirst = DB::transaction(function () use (
            $order,
            $status,
            $inventoryReservationService
        ): bool {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Cancellation is not a refund. Keep paid orders fulfilment-safe
            // until a verified refund workflow is completed separately.
            if ($status === 'cancelled' && $lockedOrder->payment_status === 'paid') {
                return true;
            }

            $lockedOrder->update([
                'status' => $status,
            ]);

            if ($status === 'cancelled') {
                $inventoryReservationService->releaseForOrder($lockedOrder);
            }

            return false;
        });

        if ($requiresRefundFirst) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with(
                    'error',
                    'این سفارش پرداخت موفق دارد. لغو سفارش بدون تأیید بازپرداخت مجاز نیست؛ ابتدا بازپرداخت را از مسیر رسمی درگاه پیگیری کنید.'
                );
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with(
                'success',
                'وضعیت سفارش با موفقیت بروزرسانی شد.'
            );
    }
}
