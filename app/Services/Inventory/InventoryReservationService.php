<?php

namespace App\Services\Inventory;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class InventoryReservationService
{
    /**
     * Deduct stock once for a new order or re-reserve it after an unpaid
     * attempt has been safely released.
     *
     * The caller may already be inside a transaction; Laravel uses a nested
     * transaction/savepoint, so order creation and stock updates still commit
     * or roll back together.
     */
    public function reserveForOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->reserveLockedOrder($lockedOrder);
        });
    }

    /**
     * Return stock for an unpaid reservation exactly once.
     */
    public function releaseForOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedOrder->inventory_reserved_at === null
                || $lockedOrder->inventory_committed_at !== null
                || $lockedOrder->payment_status === 'paid'
            ) {
                return;
            }

            $items = OrderItem::query()
                ->where('order_id', $lockedOrder->id)
                ->orderBy('product_id')
                ->orderBy('id')
                ->get();

            $productRequirements = [];
            $variantRequirements = [];
            $variantOwners = [];
            $allProductIds = [];

            foreach ($items as $item) {
                $quantity = max(0, (int) $item->quantity);
                $productId = $item->product_id === null
                    ? null
                    : (int) $item->product_id;

                $variantIds = $this->selectedVariantIds($item);

                if ($variantIds !== []) {
                    foreach ($variantIds as $variantId) {
                        $variantRequirements[$variantId] =
                            ($variantRequirements[$variantId] ?? 0) + $quantity;

                        $variantOwners[$variantId][$productId ?? 0] = true;
                    }

                    if ($productId !== null) {
                        $allProductIds[$productId] = true;
                    }

                    continue;
                }

                if ($productId !== null && $quantity > 0) {
                    $productRequirements[$productId] =
                        ($productRequirements[$productId] ?? 0) + $quantity;
                    $allProductIds[$productId] = true;
                }
            }

            $products = $allProductIds === []
                ? collect()
                : Product::withTrashed()
                    ->whereIn('id', array_keys($allProductIds))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

            $variants = $variantRequirements === []
                ? collect()
                : ProductVariant::query()
                    ->whereIn('id', array_keys($variantRequirements))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

            foreach ($productRequirements as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product) {
                    Log::warning('Inventory could not be restored because a product no longer exists.', [
                        'order_id' => $lockedOrder->id,
                        'product_id' => $productId,
                    ]);

                    continue;
                }

                $product->increment('stock', $quantity);
            }

            $missingVariantIds = [];

            foreach ($variantRequirements as $variantId => $quantity) {
                $variant = $variants->get($variantId);
                $owners = array_keys($variantOwners[$variantId] ?? []);

                if (
                    ! $variant
                    || count($owners) !== 1
                    || (int) $variant->product_id !== (int) $owners[0]
                ) {
                    $missingVariantIds[] = $variantId;
                    continue;
                }

                $variant->increment('stock', $quantity);
            }

            $attributes = [
                'inventory_reserved_at' => null,
                'inventory_released_at' => now(),
            ];

            if ($missingVariantIds !== []) {
                $attributes['inventory_issue_at'] = now();
                $attributes['inventory_issue_reason'] =
                    'یک یا چند تنوع محصول پیش از آزادسازی موجودی حذف یا تغییر کرده‌اند؛ موجودی این تنوع‌ها باید دستی بررسی شود.';

                Log::warning('Inventory release was incomplete because a product variant no longer exists.', [
                    'order_id' => $lockedOrder->id,
                    'variant_ids' => $missingVariantIds,
                ]);
            }

            $lockedOrder->forceFill($attributes)->save();
        });
    }

    /**
     * Mark reserved inventory as committed after payment verification.
     *
     * A late verified payment can arrive after a reservation was released.
     * In that case we try to reserve again. If the stock has since sold out,
     * the payment remains recorded as successful and the order receives an
     * explicit inventory issue for the admin to resolve rather than silently
     * decrementing stock below zero or losing the paid order.
     */
    public function commitForPaidOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->inventory_committed_at !== null) {
                return;
            }

            if ($lockedOrder->inventory_reserved_at === null) {
                try {
                    $this->reserveLockedOrder($lockedOrder);
                } catch (RuntimeException $exception) {
                    $lockedOrder->forceFill([
                        'inventory_reserved_at' => null,
                        'inventory_committed_at' => now(),
                        'inventory_issue_at' => now(),
                        'inventory_issue_reason' => $exception->getMessage(),
                        'status' => $lockedOrder->status === 'cancelled'
                            ? 'pending'
                            : $lockedOrder->status,
                    ])->save();

                    Log::critical('Payment was verified but inventory could not be re-reserved. Admin action is required.', [
                        'order_id' => $lockedOrder->id,
                        'order_number' => $lockedOrder->order_number,
                        'reason' => $exception->getMessage(),
                    ]);

                    return;
                }
            }

            $lockedOrder->forceFill([
                'inventory_committed_at' => now(),
                'inventory_issue_at' => null,
                'inventory_issue_reason' => null,
                'status' => $lockedOrder->status === 'cancelled'
                    ? 'pending'
                    : $lockedOrder->status,
            ])->save();
        });
    }

    protected function reserveLockedOrder(Order $order): void
    {
        if (
            $order->inventory_committed_at !== null
            || $order->inventory_reserved_at !== null
        ) {
            return;
        }

        $items = OrderItem::query()
            ->where('order_id', $order->id)
            ->orderBy('product_id')
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            throw new RuntimeException('سفارش آیتمی ندارد و موجودی آن قابل رزرو نیست.');
        }

        $productRequirements = [];
        $variantRequirements = [];
        $variantOwners = [];
        $allProductIds = [];

        foreach ($items as $item) {
            $quantity = (int) $item->quantity;

            if ($quantity < 1) {
                throw new RuntimeException('تعداد یکی از اقلام سفارش معتبر نیست.');
            }

            if ($item->product_id === null) {
                throw new RuntimeException('یکی از محصولات این سفارش دیگر در دسترس نیست.');
            }

            $productId = (int) $item->product_id;
            $allProductIds[$productId] = true;
            $variantIds = $this->selectedVariantIds($item);

            if ($variantIds === []) {
                $productRequirements[$productId] =
                    ($productRequirements[$productId] ?? 0) + $quantity;

                continue;
            }

            foreach ($variantIds as $variantId) {
                $variantRequirements[$variantId] =
                    ($variantRequirements[$variantId] ?? 0) + $quantity;

                $variantOwners[$variantId][$productId] = true;
            }
        }

        $products = Product::withTrashed()
            ->whereIn('id', array_keys($allProductIds))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($products->count() !== count($allProductIds)) {
            throw new RuntimeException('یکی از محصولات سفارش دیگر در پایگاه داده موجود نیست.');
        }

        $variants = $variantRequirements === []
            ? collect()
            : ProductVariant::query()
                ->whereIn('id', array_keys($variantRequirements))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

        foreach ($variantRequirements as $variantId => $quantity) {
            $variant = $variants->get($variantId);
            $owners = array_keys($variantOwners[$variantId] ?? []);

            if (
                ! $variant
                || count($owners) !== 1
                || (int) $variant->product_id !== (int) $owners[0]
                || ! $variant->is_active
            ) {
                throw new RuntimeException('یکی از تنوع‌های انتخاب‌شده برای سفارش دیگر قابل خرید نیست.');
            }

            if ((int) $variant->stock < $quantity) {
                throw new RuntimeException('موجودی یکی از تنوع‌های انتخاب‌شده برای سفارش کافی نیست.');
            }
        }

        foreach ($productRequirements as $productId => $quantity) {
            $product = $products->get($productId);

            if ((int) $product->stock < $quantity) {
                throw new RuntimeException('موجودی محصول «'.$product->name.'» برای این سفارش کافی نیست.');
            }
        }

        // Validate every stock row before changing any row; the enclosing
        // transaction then makes the reservation all-or-nothing.
        foreach ($productRequirements as $productId => $quantity) {
            $products->get($productId)->decrement('stock', $quantity);
        }

        foreach ($variantRequirements as $variantId => $quantity) {
            $variants->get($variantId)->decrement('stock', $quantity);
        }

        $order->forceFill([
            'inventory_reserved_at' => now(),
            'inventory_released_at' => null,
            'inventory_issue_at' => null,
            'inventory_issue_reason' => null,
        ])->save();
    }

    /**
     * @return array<int, int>
     */
    protected function selectedVariantIds(OrderItem $item): array
    {
        $options = collect($item->variant_options ?? []);
        $ids = $options
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === [] && $item->product_variant_id) {
            $ids = [(int) $item->product_variant_id];
        }

        return $ids;
    }
}
