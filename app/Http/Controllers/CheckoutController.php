<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Services\Installments\InstallmentPlanService;
use App\Services\Inventory\InventoryReservationService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Show checkout page.
     */
    public function index(): View
    {
        $cart = $this->getActiveCart();

        $cart->load([
            'items.product.category',
            'items.product.images',
            'items.variant',
        ]);

        abort_if(
            $cart->items->isEmpty(),
            404,
            'سبد خرید خالی است.'
        );

        $defaultAddress = Auth::user()
            ->defaultAddress()
            ->first();

        return view('checkout.index', [
            'cart' => $cart,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * @param Order $order
     * @return View
     */
    public function installment(
        Order $order
    ): View {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $order->load([
            'items.product.images',
            'installments',
        ]);

        /*
        |--------------------------------------------------------------------------
        | The installment plan must already exist.
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $order->payment_method === 'installment'
            && $order->installment_enabled === true,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Make sure the order actually has installment rows.
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $order->installments->isNotEmpty(),
            404
        );

        return view(
            'checkout.installment',
            [
                'order' => $order,
            ]
        );
    }
    /**
     * Create order from the active cart.
     */
    public function placeOrder(
        PlaceOrderRequest $request,
        InventoryReservationService $inventoryReservationService
    ): RedirectResponse {
        $validated = $request->validated();

        try {
            $cart = $this->getActiveCart();

            $order = DB::transaction(function () use (
                $cart,
                $validated,
                $inventoryReservationService
            ) {
                // Lock and reload the active cart inside the same transaction
                // that creates the order, so a duplicate checkout submission
                // cannot snapshot the same cart items twice.
                $lockedCart = Cart::query()
                    ->whereKey($cart->getKey())
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

                $shippingCost = 0;
                $discount = 0;
                $total = $subtotal + $shippingCost - $discount;

                $order = Order::create([
                    'user_id' => Auth::id(),
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
                $inventoryReservationService->reserveForOrder($order);

                // Clear the cart before commit; the lock prevents concurrent
                // submissions from creating multiple orders from the same cart.
                $lockedCart->items()->delete();

                return $order;
            });

            if (! $order) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', 'سبد خرید شما خالی است.');
            }

            return redirect()
                ->route('checkout.payment', $order)
                ->with('success', 'سفارش شما با موفقیت ثبت شد.');
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'ثبت سفارش انجام نشد. لطفاً دوباره تلاش کنید.');
        }
    }

    /**
     * Show payment selection page.
     */
    public function payment(
        Order $order
    ): View {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $order->load([
            'items.product.images',
            'latestPayment',
            'installments',
        ]);

        return view('checkout.payment', [
            'order' => $order,
        ]);
    }

    /**
     * Start DigiPay / SnapPay / TorobPay installment payment.
     */
    public function startInstallmentPayment(
        Request $request,
        Order $order,
        PaymentService $paymentService
    ): RedirectResponse {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $validated = $request->validate([
            'gateway' => [
                'required',
                'string',
                'in:digipay,snappay,torobpay',
            ],
        ]);

        try {
            $result = $paymentService
                ->startInstallmentPayment(
                    $order,
                    $validated['gateway']
                );

            if (!($result['success'] ?? false)) {
                return back()->with(
                    'error',
                    $result['message']
                    ?? 'امکان شروع پرداخت وجود ندارد.'
                );
            }

            if (
                empty(
                $result['redirect_url']
                )
            ) {
                return back()->with(
                    'error',
                    'درگاه پرداخت لینک انتقال معتبری برنگرداند.'
                );
            }

            return redirect()->away(
                $result['redirect_url']
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'خطایی هنگام شروع پرداخت رخ داد.'
            );
        }
    }

    /**
     * Start Livora internal installment plan.
     *
     * Example:
     * 50% cash + remaining amount by cheque.
     */
    public function startInternalInstallment(
        Order $order,
        InstallmentPlanService $installmentPlanService
    ): RedirectResponse {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        try {
            $installmentPlanService->create($order);

            return redirect()
                ->route(
                    'checkout.installment',
                    $order
                )
                ->with(
                    'success',
                    'طرح خرید اقساطی برای سفارش ایجاد شد.'
                );

        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                        ?: 'امکان ایجاد طرح اقساطی وجود ندارد.'
                );
        }
    }

    /**
     * Preview Livora installment plan.
     *
     * This endpoint will be useful later for
     * AJAX/calculator UI.
     */
    public function installmentPreview(
        Order $order,
        InstallmentPlanService $installmentPlanService
    ): RedirectResponse|array {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        try {
            return $installmentPlanService
                ->preview($order);
        } catch (Throwable $e) {
            report($e);

            return [
                'enabled' => false,
                'message' =>
                    $e->getMessage()
                        ?: 'طرح اقساطی قابل محاسبه نیست.',
            ];
        }
    }

    /**
     * Handle external payment callback.
     */
    public function paymentCallback(
        Request $request,
        string $gateway,
        PaymentService $paymentService
    ): RedirectResponse {
        abort_unless(
            in_array(
                $gateway,
                [
                    'digipay',
                    'snappay',
                    'torobpay',
                ],
                true
            ),
            404
        );

        try {
            $result = $paymentService
                ->handleCallback(
                    $gateway,
                    $request->all()
                );

            if (!($result['success'] ?? false)) {
                return redirect()
                    ->route('home')
                    ->with(
                        'error',
                        $result['message']
                        ?? 'پرداخت انجام نشد.'
                    );
            }

            $orderId =
                $result['order_id']
                ?? null;

            if ($orderId) {
                return redirect()
                    ->route(
                        'account.orders.show',
                        $orderId
                    )
                    ->with(
                        'success',
                        'پرداخت با موفقیت تأیید شد.'
                    );
            }

            return redirect()
                ->route('home')
                ->with(
                    'success',
                    'پرداخت با موفقیت تأیید شد.'
                );
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'خطایی در تأیید پرداخت رخ داد.'
                );
        }
    }

    /**
     * Get active customer cart.
     */
    protected function getActiveCart(): Cart
    {
        return Cart::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'status',
                'active'
            )
            ->firstOrCreate(
                [
                    'user_id' =>
                        Auth::id(),

                    'status' =>
                        'active',
                ],
                [
                    'session_id' =>
                        null,
                ]
            );
    }

    /**
     * Generate unique order number.
     */
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
