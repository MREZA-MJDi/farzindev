<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Auth::user()
            ->orders()
            ->with([
                'items.product.images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'items.product.images.media:id,path',
            ])
            ->latest()
            ->paginate(10);

        return view('account.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'items.product.images' => fn ($query) => $query
                ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                ->limit(1),
            'items.product.images.media:id,path',
            'items.variant',
            'latestPayment',
        ]);

        return view('account.orders.show', [
            'order' => $order,
        ]);
    }
}
