<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use App\Models\Product;
use App\Models\SeoRedirect;
use App\Models\StorefrontSetting;
use App\Support\ProductPurchaseRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View|RedirectResponse
    {
        $product = Product::query()
            ->with([
                'category',
                'images.media',
                'variants.images.media',
            ])
            ->active()
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            $redirect = SeoRedirect::query()
                ->where('source_path', route('product.show', $slug, false))
                ->where('is_active', true)
                ->first();

            if ($redirect) {
                return redirect()->to($redirect->target_url, $redirect->status_code);
            }

            abort(404);
        }

        // Never render broken/empty image records as gallery slides.
        $product->setRelation(
            'images',
            $product->images->filter(fn ($image) => filled($image->url))->values()
        );

        // Product::variants() already scopes to active variants. Stock rules
        // must use the same active option set as the cart and checkout.
        $variantGroups = $product->variants->groupBy('type');
        $canPurchase = ProductPurchaseRules::isAvailable($product, $product->variants);

        // This is the largest quantity that could be purchased for at least
        // one combination. A small client-side listener tightens it further
        // after the shopper selects specific options.
        $maxPurchaseQuantity = $variantGroups->isNotEmpty()
            ? (int) $variantGroups->map(
                fn ($options) => (int) $options->max('stock')
            )->min()
            : (int) $product->stock;
        $maxPurchaseQuantity = max(1, min(99, $maxPurchaseQuantity));

        $priceMinimum = (float) $product->price;
        $priceMaximum = (float) $product->price;
        foreach ($variantGroups as $options) {
            $availableOptions = $options->filter(fn ($option) => (int) $option->stock > 0);

            if ($availableOptions->isEmpty()) {
                continue;
            }

            $priceMinimum += (float) $availableOptions->min('price_adjustment');
            $priceMaximum += (float) $availableOptions->max('price_adjustment');
        }

        $productPriceRange = [
            'min' => max(0, round($priceMinimum, 2)),
            'max' => max(0, round($priceMaximum, 2)),
        ];

        $installmentPlan = ProductPurchaseRules::hasValidInstallmentPlan($product, $product->variants)
            ? $this->resolveInstallmentPlan($product)
            : null;
        $safeShortDescription = $this->publicCopy($product->short_description);
        $safeDescription = $this->publicCopy($product->description);

        $relatedProducts = Product::query()
            ->active()
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('images', fn (Builder $imageQuery) => $imageQuery
                ->where(function (Builder $imageSource): void {
                    $imageSource
                        ->where(fn (Builder $path) => $path->whereNotNull('path')->where('path', '<>', ''))
                        ->orWhereHas('media');
                }))
            ->where(function (Builder $stockQuery): void {
                $stockQuery
                    ->where(function (Builder $simpleProduct): void {
                        $simpleProduct->whereDoesntHave('variants')->where('stock', '>', 0);
                    })
                    ->orWhere(function (Builder $configuredProduct): void {
                        $configuredProduct
                            ->whereHas('variants')
                            ->whereNotExists(function (\Illuminate\Database\Query\Builder $unavailableType): void {
                                $unavailableType
                                    ->selectRaw('1')
                                    ->from('product_variants as required_variants')
                                    ->whereColumn('required_variants.product_id', 'products.id')
                                    ->where('required_variants.is_active', true)
                                    ->groupBy('required_variants.type')
                                    ->havingRaw('SUM(CASE WHEN required_variants.stock > 0 THEN 1 ELSE 0 END) = 0');
                            });
                    });
            })
            ->with([
                'category:id,name,slug',
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->limit(1),
                'images.media:id,path',
                'variants:id,product_id,type,stock',
            ])
            ->latest()
            ->limit(60)
            ->get()
            ->filter(function (Product $related): bool {
                $images = $related->images;
                $groups = $related->variants->groupBy('type');
                $canPurchase = $groups->isNotEmpty()
                    ? $groups->every(fn ($options) => $options->contains(fn ($option) => (int) $option->stock > 0))
                    : (int) $related->stock > 0;

                return $canPurchase && $images->contains(fn ($image) => filled($image->url));
            })
            ->take(4)
            ->values();

        $contactSetting = ContactSetting::query()
            ->where('is_active', true)
            ->first(['phone', 'whatsapp']);

        $shippingSettings = StorefrontSetting::query()
            ->whereIn('key', ['shipping_flat_rate', 'shipping_free_threshold'])
            ->pluck('value', 'key')
            ->all();

        return view('product.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'variantGroups' => $variantGroups,
            'canPurchase' => $canPurchase,
            'maxPurchaseQuantity' => $maxPurchaseQuantity,
            'productPriceRange' => $productPriceRange,
            'installmentPlan' => $installmentPlan,
            'safeShortDescription' => $safeShortDescription,
            'safeDescription' => $safeDescription,
            'contactSetting' => $contactSetting,
            'shippingSettings' => $shippingSettings,
        ]);
    }

    /**
     * Build installment amounts in integer cents to prevent floating-point
     * rounding from showing totals different from the exact product price.
     *
     * @return array<string, mixed>|null
     */
    private function resolveInstallmentPlan(Product $product): ?array
    {
        $cashPercent = (int) ($product->installment_cash_percent ?? 0);
        $chequeCount = (int) ($product->installment_cheque_count ?? 0);
        $intervalMonths = (int) ($product->installment_interval_months ?? 0);

        if (
            ! $product->installment_enabled
            || $product->variants->contains(fn ($variant) => abs((float) $variant->price_adjustment) >= 0.01)
            || $cashPercent < 1
            || $cashPercent > 99
            || $product->installment_remainder_method !== 'cheque'
            || $chequeCount < 1
            || $chequeCount > 36
            || $intervalMonths < 1
            || $intervalMonths > 24
            || (float) $product->price <= 0
        ) {
            return null;
        }

        $priceCents = (int) round((float) $product->price * 100);
        $cashCents = (int) round($priceCents * $cashPercent / 100);
        $remainderCents = $priceCents - $cashCents;

        if ($priceCents < 1 || $cashCents < 1 || $remainderCents < $chequeCount) {
            return null;
        }

        $baseChequeCents = intdiv($remainderCents, $chequeCount);
        $distributedCents = 0;
        $amounts = [];

        for ($index = 1; $index <= $chequeCount; $index++) {
            $amountCents = $index === $chequeCount
                ? $remainderCents - $distributedCents
                : $baseChequeCents;

            if ($amountCents < 1) {
                return null;
            }

            $amounts[] = $amountCents / 100;
            $distributedCents += $amountCents;
        }

        $cashAmount = $cashCents / 100;
        $deferredAmount = $remainderCents / 100;
        $total = $cashAmount + array_sum($amounts);
        $price = $priceCents / 100;

        return [
            'cash_percent' => $cashPercent,
            'cash_amount' => $cashAmount,
            'deferred_amount' => $deferredAmount,
            'cheque_count' => $chequeCount,
            'interval_months' => $intervalMonths,
            'cheque_amounts' => $amounts,
            'without_fee' => abs($total - $price) < 0.001,
        ];
    }

    private function publicCopy(?string $value): ?string
    {
        $copy = trim((string) $value);

        if ($copy === '') {
            return null;
        }

        $placeholderPatterns = [
            'این رکورد برای بررسی چیدمان',
            'اطلاعات نمایشی برای پیش‌نمایش',
            'پیش‌نمایش کاتالوگ',
            'پیش از انتشار نهایی',
            'مشخصات نهایی باید',
            'رکورد صرفاً برای تست خودکار',
        ];

        foreach ($placeholderPatterns as $pattern) {
            if (Str::contains($copy, $pattern)) {
                return null;
            }
        }

        return $copy;
    }
}
