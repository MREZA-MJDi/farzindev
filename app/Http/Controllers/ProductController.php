<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use App\Models\Product;
use App\Models\StorefrontSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View
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
            ->firstOrFail();

        $product->setRelation('images', $product->images->filter(fn ($image) => filled($image->url))->values());
        $variantGroups = $product->variants->groupBy('type');
        $canPurchase = $variantGroups->isNotEmpty()
            ? $variantGroups->every(fn ($options) => $options->contains(
                fn ($option) => (int) $option->stock > 0
            ))
            : (int) $product->stock > 0;

        $maxPurchaseQuantity = $variantGroups->isNotEmpty()
            ? max(1, min(99, (int) $product->variants->max('stock')))
            : max(1, min(99, (int) $product->stock));

        $installmentPlan = $this->resolveInstallmentPlan($product);
        $safeShortDescription = $this->publicCopy($product->short_description);
        $safeDescription = $this->publicCopy($product->description);

        $relatedProducts = Product::query()
            ->active()
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('images')
            ->where(function (Builder $stockQuery): void {
                $stockQuery
                    ->whereHas('variants', fn (Builder $variants) => $variants->where('stock', '>', 0))
                    ->orWhere(function (Builder $simpleProduct): void {
                        $simpleProduct
                            ->whereDoesntHave('variants')
                            ->where('stock', '>', 0);
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
            ->limit(30)
            ->get()
            ->filter(function (Product $related): bool {
                $groups = $related->variants->groupBy('type');
                $canPurchase = $groups->isNotEmpty()
                    ? $groups->every(fn ($options) => $options->contains(fn ($option) => (int) $option->stock > 0))
                    : (int) $related->stock > 0;

                return $canPurchase && $related->images->contains(fn ($image) => filled($image->url));
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
            'installmentPlan' => $installmentPlan,
            'safeShortDescription' => $safeShortDescription,
            'safeDescription' => $safeDescription,
            'contactSetting' => $contactSetting,
            'shippingSettings' => $shippingSettings,
        ]);
    }

    private function resolveInstallmentPlan(Product $product): ?array
    {
        $cashPercent = (int) ($product->installment_cash_percent ?? 0);
        $chequeCount = (int) ($product->installment_cheque_count ?? 0);
        $intervalMonths = (int) ($product->installment_interval_months ?? 0);

        if (
            ! $product->installment_enabled
            || $cashPercent < 1
            || $cashPercent > 99
            || $product->installment_remainder_method !== 'cheque'
            || $chequeCount < 1
            || $intervalMonths < 1
            || (float) $product->price <= 0
        ) {
            return null;
        }

        $price = round((float) $product->price, 2);
        $cashAmount = round($price * ($cashPercent / 100), 2);
        $deferredAmount = round($price - $cashAmount, 2);
        $baseCheque = round($deferredAmount / $chequeCount, 2);
        $amounts = [];
        $distributed = 0.0;

        for ($index = 1; $index <= $chequeCount; $index++) {
            $amount = $index === $chequeCount
                ? round($deferredAmount - $distributed, 2)
                : $baseCheque;

            if ($amount <= 0) {
                return null;
            }

            $amounts[] = $amount;
            $distributed = round($distributed + $amount, 2);
        }

        $total = round($cashAmount + array_sum($amounts), 2);

        return [
            'cash_percent' => $cashPercent,
            'cash_amount' => $cashAmount,
            'deferred_amount' => $deferredAmount,
            'cheque_count' => $chequeCount,
            'interval_months' => $intervalMonths,
            'cheque_amounts' => $amounts,
            'without_fee' => $total === $price,
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
        ];

        foreach ($placeholderPatterns as $pattern) {
            if (Str::contains($copy, $pattern)) {
                return null;
            }
        }

        return $copy;
    }
}
