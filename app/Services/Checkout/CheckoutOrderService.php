<?php

namespace App\Services\Checkout;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StorefrontSetting;
use App\Services\Inventory\InventoryReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CheckoutOrderService
{
    public function __construct(
        private readonly InventoryReservationService $inventoryReservationService
    ) {}

    /**
     * Validate live catalogue data and create an order in one transaction.
     *
     * Product/category/variant locks are taken in a stable order so concurrent
     * checkouts cannot use stale prices or reserve the same available stock.
     *
     * @param array<string, mixed> $validated
     */
    public function placeOrder(Cart $cart, int $userId, array $validated): ?Order
    {
        $order = DB::transaction(function () use ($cart, $validated, $userId) {
            // Lock and reload the active cart inside the same transaction
            // that creates the order, so a duplicate checkout submission
            // cannot snapshot the same cart items twice.
            $lockedCart = Cart::query()
                ->whereKey($cart->getKey())
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            $lockedCart->load([
                'items.product',
                'items.variant',
            ]);

            if ($lockedCart->items->isEmpty()) {
                return null;
            }

            // Lock catalogue rows in a stable order and price the order
            // from current database values, never from stale cart snapshots.
            $productIds = $lockedCart->items
                ->pluck('product_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values();

            $lockedProducts = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $categoryIds = $lockedProducts
                ->pluck('category_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values();

            $activeCategoryIds = Category::query()
                ->whereIn('id', $categoryIds)
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            // Product option rows are also locked in a consistent order.
            $activeVariantsByProduct = ProductVariant::query()
                ->whereIn('product_id', $productIds)
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->groupBy('product_id');

            $subtotal = 0;
            $checkedLines = [];

            foreach ($lockedCart->items as $item) {
                $product = $lockedProducts->get((int) $item->product_id);

                if (
                    ! $product
                    || $product->status !== 'active'
                    || ! $activeCategoryIds->contains((int) $product->category_id)
                ) {
                    throw new \RuntimeException(
                        'یکی از محصولات سبد یا دسته‌بندی آن دیگر برای خرید در دسترس نیست. سبد را بررسی کنید.'
                    );
                }

                $item->setRelation('product', $product);

                $selectedVariantIds = $item->selectedVariantIds();
                $availableVariants = $activeVariantsByProduct->get(
                    $product->id,
                    collect()
                );

                $variants = collect($selectedVariantIds)
                    ->map(fn ($id) => $availableVariants->firstWhere('id', (int) $id))
                    ->filter()
                    ->values();

                if (count($selectedVariantIds) !== $variants->count()) {
                    throw new \RuntimeException(
                        "یکی از گزینه‌های انتخاب‌شده برای «{$product->name}» دیگر در دسترس نیست."
                    );
                }

                $selectedTypes = $variants->pluck('type')->unique()->values();
                $currentTypes = $availableVariants->pluck('type')->unique()->values();

                if (
                    $selectedTypes->count() !== $variants->count()
                    || $currentTypes->diff($selectedTypes)->isNotEmpty()
                ) {
                    throw new \RuntimeException(
                        "گزینه‌های محصول «{$product->name}» تغییر کرده‌اند. لطفاً سبد خرید را به‌روزرسانی کنید."
                    );
                }

                $stock = $variants->isNotEmpty()
                    ? (int) $variants->min('stock')
                    : (int) $product->stock;

                if ($stock < 1 || (int) $item->quantity > $stock) {
                    throw new \RuntimeException(
                        "موجودی محصول «{$product->name}» کافی نیست."
                    );
                }

                $unitPrice = max(
                    0,
                    round(
                        (float) $product->price
                        + (float) $variants->sum(
                            fn (ProductVariant $variant) => (float) $variant->price_adjustment
                        ),
                        2
                    )
                );

                $variantSnapshot = $variants
                    ->map(fn (ProductVariant $variant) => [
                        'id' => (int) $variant->id,
                        'type' => (string) $variant->type,
                        'name' => (string) $variant->name,
                        'value' => (string) $variant->value,
                        'sku' => $variant->sku,
                        'color_hex' => $variant->color_hex,
                        'price_adjustment' => (float) $variant->price_adjustment,
                        'stock' => (int) $variant->stock,
                    ])
                    ->values()
                    ->all();

                $checkedLines[(int) $item->id] = [
                    'unit_price' => $unitPrice,
                    'sku' => $variants->count() === 1
                        ? ($variants->first()->sku ?: $product->sku)
                        : $product->sku,
                    'product_variant_id' => $variants->first()?->id,
                    'variant_options' => $variantSnapshot ?: null,
                ];

                $subtotal += $unitPrice * (int) $item->quantity;
            }

            $shippingFlatRate = max(
                0,
                (int) (StorefrontSetting::query()
                    ->where('key', 'shipping_flat_rate')
                    ->value('value') ?? 0)
            );
            $freeShippingThreshold = max(
                0,
                (int) (StorefrontSetting::query()
                    ->where('key', 'shipping_free_threshold')
                    ->value('value') ?? 0)
            );

            $shippingCost = $freeShippingThreshold > 0
                && $subtotal >= $freeShippingThreshold
                ? 0
                : $shippingFlatRate;

            // Discounts remain zero until a validated promotion system exists.
            $discount = 0;
            $total = $subtotal + $shippingCost - $discount;

            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'online',
                'payment_provider' => null,
                'installment_enabled' => false,
                'installment_cash_percent' => null,
                'installment_cash_amount' => null,
                'installment_deferred_amount' => null,
                'installment_remainder_method' => null,
                'installment_cheque_count' => null,
                'installment_interval_months' => null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => $total,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'province' => $validated['province'],
                'city' => $validated['city'],
                'address' => $validated['address'],
                'postal_code' => $validated['postal_code'],
                'unit' => $validated['unit'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lockedCart->items as $item) {
                $line = $checkedLines[(int) $item->id];

                $order->items()->create([
                    'product_id' => $item->product_id,
                    // Keep the legacy link for single-variant integrations,
                    // and snapshot the current option values at checkout.
                    'product_variant_id' => $line['product_variant_id'],
                    'variant_options' => $line['variant_options'],
                    'product_name' => $item->product->name,
                    'sku' => $line['sku'],
                    'quantity' => $item->quantity,
                    'unit_price' => $line['unit_price'],
                    'total' => $line['unit_price'] * (int) $item->quantity,
                ]);
            }

            // Reserve stock inside the order transaction. Product and
            // variant rows are locked and quantities aggregated, so two
            // concurrent checkouts cannot sell the same stock twice.
            $this->inventoryReservationService->reserveForOrder($order);

            // Clear the cart before commit; the lock prevents concurrent
            // submissions from creating multiple orders from the same cart.
            $lockedCart->items()->delete();

            return $order;
        });
        return $order;
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number =
                'FZ-'
                . now()->format('ymd')
                . '-'
                . strtoupper(
                    Str::random(6)
                );
        } while (
            Order::query()
                ->where(
                    'order_number',
                    $number
                )
                ->exists()
        );

        return $number;
    }
}
