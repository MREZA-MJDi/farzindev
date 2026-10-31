<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Models\Cart;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->getCart($request);

        $cart->load([
            'items.product.category',
            'items.product.images' => fn ($query) => $query
                ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                ->limit(1),
            'items.product.images.media:id,path',
            'items.product.variants',
            'items.variant',
        ]);

        $currentSubtotal = 0.0;
        $cartReadyForCheckout = $cart->items->isNotEmpty();

        foreach ($cart->items as $item) {
            $item->setAttribute('current_unit_price', null);
            $item->setAttribute('current_line_total', 0.0);
            $item->setAttribute('current_stock', 0);
            $item->setAttribute('price_changed', false);
            $item->setAttribute('is_currently_available', false);
            $item->setAttribute('cart_issue_message', null);

            $product = $item->product;

            if (! $product) {
                $item->setAttribute(
                    'cart_issue_message',
                    'این محصول دیگر در فروشگاه در دسترس نیست؛ آن را از سبد حذف کنید.'
                );
                $cartReadyForCheckout = false;

                continue;
            }

            if (
                $product->status !== 'active'
                || ! $product->category
                || ! $product->category->is_active
            ) {
                $item->setAttribute(
                    'cart_issue_message',
                    'این محصول یا دسته‌بندی آن غیرفعال شده است؛ آن را از سبد حذف کنید.'
                );
                $cartReadyForCheckout = false;

                continue;
            }

            $activeVariants = $product->variants;
            $selectedIds = $item->selectedVariantIds();
            $selectedVariants = $activeVariants
                ->whereIn('id', $selectedIds)
                ->values();

            $activeTypes = $activeVariants->pluck('type')->unique()->values();
            $selectedTypes = $selectedVariants->pluck('type')->unique()->values();

            $selectionIsValid = $activeVariants->isEmpty()
                ? $selectedIds === []
                : count($selectedIds) === $selectedVariants->count()
                    && $selectedVariants->pluck('type')->unique()->count() === $selectedVariants->count()
                    && $activeTypes->diff($selectedTypes)->isEmpty()
                    && $selectedTypes->diff($activeTypes)->isEmpty();

            if (! $selectionIsValid) {
                $item->setAttribute(
                    'cart_issue_message',
                    'ویژگی‌های این محصول تغییر کرده است؛ آن را حذف کنید و با گزینه‌های فعلی دوباره اضافه کنید.'
                );
                $cartReadyForCheckout = false;

                continue;
            }

            $stock = $selectedVariants->isNotEmpty()
                ? (int) $selectedVariants->min('stock')
                : (int) $product->stock;

            $currentUnitPrice = max(
                0,
                round(
                    (float) $product->price
                    + (float) $selectedVariants->sum(
                        fn (ProductVariant $variant) => (float) $variant->price_adjustment
                    ),
                    2
                )
            );

            $currentLineTotal = $currentUnitPrice * (int) $item->quantity;
            $priceChanged = abs($currentUnitPrice - (float) $item->unit_price) >= 0.01;

            $item->setAttribute('current_unit_price', $currentUnitPrice);
            $item->setAttribute('current_line_total', $currentLineTotal);
            $item->setAttribute('current_stock', $stock);
            $item->setAttribute('price_changed', $priceChanged);
            $item->setAttribute(
                'is_currently_available',
                $stock > 0 && (int) $item->quantity <= $stock
            );

            if ($priceChanged) {
                $item->setAttribute(
                    'cart_issue_message',
                    'قیمت این کالا تغییر کرده است؛ قیمت فعلی را بررسی و با «تأیید قیمت و تعداد» ثبت کنید.'
                );
                $cartReadyForCheckout = false;
            } elseif ($stock < 1) {
                $item->setAttribute(
                    'cart_issue_message',
                    'این کالا فعلاً موجود نیست؛ برای ادامه آن را حذف کنید.'
                );
                $cartReadyForCheckout = false;
            } elseif ((int) $item->quantity > $stock) {
                $item->setAttribute(
                    'cart_issue_message',
                    'تعداد انتخاب‌شده بیشتر از موجودی فعلی است؛ تعداد را کاهش دهید.'
                );
                $cartReadyForCheckout = false;
            }

            $currentSubtotal += $currentLineTotal;
        }

        return view('cart.index', [
            'cart' => $cart,
            'currentSubtotal' => $currentSubtotal,
            'cartReadyForCheckout' => $cartReadyForCheckout,
        ]);
    }

    public function add(
        AddToCartRequest $request,
        Product $product
    ): RedirectResponse|JsonResponse {
        $validated = $request->validated();
        $cart = $this->getCart($request);
        $requestedQuantity = (int) $validated['quantity'];

        $result = DB::transaction(function () use (
            $cart,
            $product,
            $validated,
            $requestedQuantity
        ): array {
            // Serialize all mutations and checkout snapshots for the same cart.
            $lockedCart = Cart::query()
                ->whereKey($cart->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            $lockedProduct = Product::query()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedCategory = Category::query()
                ->whereKey($lockedProduct->category_id)
                ->lockForUpdate()
                ->first();

            if (
                $lockedProduct->status !== 'active'
                || ! $lockedCategory
                || ! $lockedCategory->is_active
            ) {
                return ['error' => 'product'];
            }

            // Lock active options in a stable order and re-check selections
            // after acquiring the lock; request validation alone can be stale.
            $currentVariants = $lockedProduct->variants()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $submittedVariants = collect($validated['variants'] ?? [])
                ->filter(fn ($id) => filled($id))
                ->mapWithKeys(fn ($id, $type) => [(string) $type => (int) $id]);

            if (
                $submittedVariants->isEmpty()
                && filled($validated['product_variant_id'] ?? null)
            ) {
                $legacyVariant = $currentVariants->firstWhere(
                    'id',
                    (int) $validated['product_variant_id']
                );

                if ($legacyVariant) {
                    $submittedVariants->put(
                        (string) $legacyVariant->type,
                        (int) $legacyVariant->id
                    );
                } else {
                    return ['error' => 'options'];
                }
            }

            $currentTypes = $currentVariants
                ->pluck('type')
                ->map(fn ($type) => (string) $type)
                ->unique()
                ->values();

            if ($currentVariants->isEmpty()) {
                if ($submittedVariants->isNotEmpty()) {
                    return ['error' => 'options'];
                }

                $variants = collect();
            } else {
                if (
                    $currentTypes->diff($submittedVariants->keys())->isNotEmpty()
                    || $submittedVariants->keys()->diff($currentTypes)->isNotEmpty()
                ) {
                    return ['error' => 'options'];
                }

                $variants = $submittedVariants
                    ->map(fn ($id, $type) => $currentVariants->first(
                        fn (ProductVariant $variant) =>
                            (string) $variant->type === (string) $type
                            && (int) $variant->id === (int) $id
                    ))
                    ->values();

                if (
                    $variants->count() !== $submittedVariants->count()
                    || $variants->pluck('type')->unique()->count() !== $currentTypes->count()
                ) {
                    return ['error' => 'options'];
                }
            }

            $availableStock = $variants->isNotEmpty()
                ? (int) $variants->min('stock')
                : (int) $lockedProduct->stock;

            $variantKey = $this->buildVariantKey($variants, $lockedProduct);
            $primaryVariantId = $variants->first()?->id;

            $itemQuery = $lockedCart->items()
                ->where('product_id', $lockedProduct->id)
                ->where(function ($query) use (
                    $variantKey,
                    $primaryVariantId,
                    $variants
                ): void {
                    $query->where('variant_key', $variantKey);

                    // Merge legacy rows written before variant snapshots existed.
                    if ($variants->count() === 1) {
                        $query->orWhere(function ($legacy) use ($primaryVariantId): void {
                            $legacy
                                ->whereNull('variant_key')
                                ->whereNull('variant_options')
                                ->where('product_variant_id', $primaryVariantId);
                        });
                    }

                    if ($variants->isEmpty()) {
                        $query->orWhere(function ($legacy): void {
                            $legacy
                                ->whereNull('variant_key')
                                ->whereNull('variant_options')
                                ->whereNull('product_variant_id');
                        });
                    }
                });

            $item = $itemQuery->lockForUpdate()->first();
            $totalQuantity = $requestedQuantity + (int) ($item?->quantity ?? 0);

            if ($availableStock < 1 || $totalQuantity > $availableStock) {
                return ['error' => 'stock'];
            }

            $unitPrice = max(
                0,
                (float) $lockedProduct->price
                    + (float) $variants->sum(
                        fn (ProductVariant $variant) => (float) $variant->price_adjustment
                    )
            );

            $variantOptions = $variants
                ->sortBy('type', SORT_NATURAL | SORT_FLAG_CASE)
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

            $payload = [
                'product_id' => $lockedProduct->id,
                'product_variant_id' => $primaryVariantId,
                'variant_key' => $variantKey,
                'variant_options' => $variantOptions ?: null,
                'quantity' => $totalQuantity,
                'unit_price' => $unitPrice,
            ];

            if ($item) {
                $item->update($payload);
            } else {
                $lockedCart->items()->create($payload);
            }

            return ['cart_id' => (int) $lockedCart->id];
        }, 3);

        if (isset($result['error'])) {
            $messages = [
                'product' => 'این محصول یا دسته‌بندی دیگر برای خرید فعال نیست.',
                'options' => 'گزینه‌های محصول تغییر کرده‌اند؛ صفحه را تازه کنید و گزینه‌ها را دوباره انتخاب کنید.',
                'stock' => 'موجودی کافی نیست؛ تعداد فعلی سبد خرید را بررسی کنید.',
            ];
            $message = $messages[$result['error']] ?? 'افزودن محصول انجام نشد.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->withErrors([
                ($result['error'] === 'stock' ? 'quantity' : 'product') => $message,
            ]);
        }

        $cart = Cart::query()
            ->with('items')
            ->findOrFail($result['cart_id']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'محصول به سبد اضافه شد.',
                'cart_count' => $cart->itemCount(),
                'subtotal' => $cart->subtotal(),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'محصول به سبد خرید اضافه شد.');
    }

    public function update(
        UpdateCartRequest $request,
        CartItem $item
    ): RedirectResponse|JsonResponse {
        $this->authorizeCartItemAccess($request, $item, 'update');
        $validated = $request->validated();
        $cartId = (int) $item->cart_id;
        $itemId = (int) $item->id;

        return DB::transaction(function () use (
            $request,
            $validated,
            $cartId,
            $itemId
        ): RedirectResponse|JsonResponse {
            // Checkout also locks the cart before reading/deleting its lines.
            $lockedCart = Cart::query()
                ->whereKey($cartId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            $item = CartItem::query()
                ->whereKey($itemId)
                ->where('cart_id', $lockedCart->id)
                ->lockForUpdate()
                ->firstOrFail();

            $item->load([
                'cart',
                'product',
                'variant',
            ]);

            $product = $item->product
                ? Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first()
                : null;

            $category = $product
                ? Category::query()
                    ->whereKey($product->category_id)
                    ->lockForUpdate()
                    ->first()
                : null;

            if (
                ! $product
                || $product->status !== 'active'
                || ! $category
                || ! $category->is_active
            ) {
                return back()->withErrors([
                    'quantity' => 'این محصول یا دسته‌بندی آن دیگر برای خرید فعال نیست؛ آن را از سبد حذف کنید.',
                ]);
            }

            $item->setRelation('product', $product);

            $selectedIds = $item->selectedVariantIds();
            $variants = $this->resolveVariantsFromCartItem($item);
            $currentVariants = $product->variants()
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $selectedTypes = $variants->pluck('type')->unique()->values();
            $currentTypes = $currentVariants->pluck('type')->unique()->values();

            $validVariantSelection = count($selectedIds) === $variants->count()
                && $variants->pluck('type')->count() === $selectedTypes->count()
                && $currentTypes->diff($selectedTypes)->isEmpty()
                && $selectedTypes->diff($currentTypes)->isEmpty();

            if (! $validVariantSelection) {
                return back()->withErrors([
                    'quantity' => 'گزینه‌های این محصول تغییر کرده‌اند؛ آن را از سبد حذف و با گزینه‌های فعلی دوباره اضافه کنید.',
                ]);
            }

            $stock = $variants->isNotEmpty()
                ? (int) $variants->min('stock')
                : (int) $product->stock;

            if ((int) $validated['quantity'] > $stock) {
                return back()->withErrors([
                    'quantity' => 'موجودی کافی نیست.',
                ]);
            }

            $unitPrice = max(
                0,
                (float) $product->price
                    + (float) $variants->sum(
                        fn (ProductVariant $variant) => (float) $variant->price_adjustment
                    )
            );

            $variantOptions = $variants
                ->sortBy('type', SORT_NATURAL | SORT_FLAG_CASE)
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

            $item->update([
                'quantity' => (int) $validated['quantity'],
                'unit_price' => $unitPrice,
                'variant_options' => $variantOptions ?: null,
            ]);

            $cart = $lockedCart->fresh('items');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'سبد به‌روزرسانی شد.',
                    'cart_count' => $cart->itemCount(),
                    'subtotal' => $cart->subtotal(),
                ]);
            }

            return back()->with('success', 'سبد خرید به‌روزرسانی شد.');
        }, 3);
    }

    public function remove(
        Request $request,
        CartItem $item
    ): RedirectResponse|JsonResponse {
        $this->authorizeCartItemAccess($request, $item, 'delete');
        $cartId = (int) $item->cart_id;
        $itemId = (int) $item->id;

        $cart = DB::transaction(function () use ($cartId, $itemId): Cart {
            $lockedCart = Cart::query()
                ->whereKey($cartId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            $lockedItem = CartItem::query()
                ->whereKey($itemId)
                ->where('cart_id', $lockedCart->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedItem->delete();

            return $lockedCart->fresh('items');
        }, 3);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'محصول از سبد حذف شد.',
                'cart_count' => $cart->itemCount(),
                'subtotal' => $cart->subtotal(),
            ]);
        }

        return back()->with('success', 'محصول از سبد حذف شد.');
    }

    public function clear(
        Request $request
    ): RedirectResponse|JsonResponse {
        $cart = $this->getCart($request);

        DB::transaction(function () use ($cart): void {
            $lockedCart = Cart::query()
                ->whereKey($cart->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            $lockedCart->items()->delete();
        }, 3);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'سبد خرید خالی شد.',
                'cart_count' => 0,
                'subtotal' => 0,
            ]);
        }

        return back()->with('success', 'سبد خرید خالی شد.');
    }

    /**
     * Customers use the model policy; guests must prove ownership through
     * the current server-side session and may only edit active guest carts.
     */
    private function authorizeCartItemAccess(
        Request $request,
        CartItem $item,
        string $ability
    ): void {
        $item->loadMissing('cart');

        if (Auth::check()) {
            $this->authorize($ability, $item);

            return;
        }

        $cart = $item->cart;
        $sessionId = (string) $request->session()->getId();
        $sessionCartId = (int) $request->session()->get('guest_cart_id', 0);

        $matchesCurrentSession = $cart !== null
            && filled($cart->session_id)
            && hash_equals((string) $cart->session_id, $sessionId);

        $matchesBoundGuestCart = $cart !== null
            && $sessionCartId === (int) $cart->id;

        abort_unless(
            $cart !== null
            && $cart->status === 'active'
            && $cart->user_id === null
            && ($matchesCurrentSession || $matchesBoundGuestCart),
            403
        );
    }

    protected function resolveSelectedVariants(
        Product $product,
        ?array $submittedVariants,
        ?int $legacyVariantId
    ) {
        $submitted = collect($submittedVariants ?? [])
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if (
            $submitted->isEmpty()
            && $legacyVariantId
        ) {
            $submitted = collect([(int) $legacyVariantId]);
        }

        if ($submitted->isEmpty()) {
            return collect();
        }

        return $product->variants()
            ->whereIn('id', $submitted->unique()->all())
            ->get()
            ->sortBy(function (ProductVariant $variant) use ($submitted) {
                return $submitted->search(
                    fn ($id) => (int) $id === (int) $variant->id
                );
            })
            ->values();
    }

    protected function resolveVariantsFromCartItem(
        CartItem $item
    ) {
        $ids = $item->selectedVariantIds();

        if (empty($ids)) {
            return collect();
        }

        return ProductVariant::query()
            ->where('product_id', $item->product_id)
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->get();
    }

    protected function buildVariantKey(
        $variants,
        Product $product
    ): string {
        if ($variants->isEmpty()) {
            return 'base';
        }

        $parts = $variants
            ->sortBy('type', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(
                fn (ProductVariant $variant) =>
                    Str::lower((string) $variant->type)
                    . ':'
                    . $variant->id
            )
            ->values()
            ->all();

        return hash(
            'sha256',
            $product->id . '|' . implode('|', $parts)
        );
    }

    protected function getCart(Request $request): Cart
    {
        if (Auth::check()) {
            return Cart::query()->firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'status' => 'active',
                ],
                [
                    'session_id' => null,
                ]
            );
        }

        $sessionId = $request->session()->getId();

        $cart = Cart::query()->firstOrCreate(
            [
                'session_id' => $sessionId,
                'status' => 'active',
            ],
            [
                'user_id' => null,
            ]
        );

        // Keep an explicit server-side ownership binding for guest carts.
        // This survives legitimate session ID rotation and makes the owner
        // check independent of trusting a cart ID supplied by the browser.
        $request->session()->put('guest_cart_id', (int) $cart->id);

        return $cart;
    }
}
