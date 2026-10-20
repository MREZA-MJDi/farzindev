<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->getCart($request);

        $cart->load([
            'items.product.category',
            'items.product.images',
            'items.variant',
        ]);

        return view('cart.index', [
            'cart' => $cart,
        ]);
    }

    public function add(
        AddToCartRequest $request,
        Product $product
    ): RedirectResponse|JsonResponse {
        $validated = $request->validated();

        $cart = $this->getCart($request);

        $variants = $this->resolveSelectedVariants(
            $product,
            $validated['variants'] ?? null,
            $validated['product_variant_id'] ?? null
        );

        $availableStock = $variants->isNotEmpty()
            ? (int) $variants->min('stock')
            : (int) $product->stock;

        $quantity = (int) $validated['quantity'];

        if ($quantity > $availableStock) {
            return back()->withErrors([
                'quantity' => 'موجودی کافی نیست.',
            ]);
        }

        $unitPrice = max(
            0,
            (float) $product->price
            + (float) $variants->sum(
                fn (ProductVariant $variant) =>
                    (float) $variant->price_adjustment
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

        $variantKey = $this->buildVariantKey(
            $variants,
            $product
        );

        $primaryVariantId = $variants->first()?->id;

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->where(function ($query) use (
                $variantKey,
                $primaryVariantId,
                $variants
            ) {
                $query->where('variant_key', $variantKey);

                /*
                 * Merge legacy one-variant cart rows created before the
                 * multi-variant snapshot fields were introduced.
                 */
                if ($variants->count() === 1) {
                    $query->orWhere(function ($legacy) use (
                        $primaryVariantId
                    ) {
                        $legacy
                            ->whereNull('variant_key')
                            ->whereNull('variant_options')
                            ->where(
                                'product_variant_id',
                                $primaryVariantId
                            );
                    });
                }

                if ($variants->isEmpty()) {
                    $query->orWhere(function ($legacy) {
                        $legacy
                            ->whereNull('variant_key')
                            ->whereNull('variant_options')
                            ->whereNull('product_variant_id');
                    });
                }
            })
            ->first();

        if ($item) {
            $quantity += (int) $item->quantity;
        }

        if ($quantity > $availableStock) {
            return back()->withErrors([
                'quantity' => 'موجودی کافی نیست.',
            ]);
        }

        $payload = [
            'product_id' => $product->id,
            'product_variant_id' => $primaryVariantId,
            'variant_key' => $variantKey,
            'variant_options' => $variantOptions ?: null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ];

        if ($item) {
            $item->update($payload);
        } else {
            $cart->items()->create(
                $payload
            );
        }

        $cart->load('items');

        if ($request->expectsJson()) {
            return response()->json([
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

        $item->load([
            'cart',
            'product',
            'variant',
        ]);

        $product = $item->product;

        if (
            ! $product
            || $product->status !== 'active'
            || ! $product->category()->where('is_active', true)->exists()
        ) {
            return back()->withErrors([
                'quantity' => 'این محصول یا دسته‌بندی آن دیگر برای خرید فعال نیست؛ آن را از سبد حذف کنید.',
            ]);
        }

        $selectedIds = $item->selectedVariantIds();
        $variants = $this->resolveVariantsFromCartItem($item);
        $currentVariants = $product->variants()
            ->where('is_active', true)
            ->orderBy('id')
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

        $cart = $item->cart->fresh('items');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'سبد به‌روزرسانی شد.',
                'cart_count' => $cart->itemCount(),
                'subtotal' => $cart->subtotal(),
            ]);
        }

        return back()->with(
            'success',
            'سبد خرید به‌روزرسانی شد.'
        );
    }

    public function remove(
        Request $request,
        CartItem $item
    ): RedirectResponse|JsonResponse {
        $this->authorizeCartItemAccess($request, $item, 'delete');

        $cart = $item->cart;

        $item->delete();

        if ($request->expectsJson()) {
            $cart = $cart->fresh('items');

            return response()->json([
                'message' => 'محصول از سبد حذف شد.',
                'cart_count' => $cart->itemCount(),
                'subtotal' => $cart->subtotal(),
            ]);
        }

        return back()->with(
            'success',
            'محصول از سبد حذف شد.'
        );
    }

    public function clear(
        Request $request
    ): RedirectResponse|JsonResponse {
        $cart = $this->getCart($request);

        $cart->items()->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'سبد خرید خالی شد.',
                'cart_count' => 0,
                'subtotal' => 0,
            ]);
        }

        return back()->with(
            'success',
            'سبد خرید خالی شد.'
        );
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
