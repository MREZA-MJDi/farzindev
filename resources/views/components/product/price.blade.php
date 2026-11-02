@props([
    'product',
    'showInstallment' => true,
    'compact' => false,
    'priceRange' => null,
])

@php
    $price = (float) $product->price;
    $compareAtPrice = $product->compare_at_price
        ? (float) $product->compare_at_price
        : null;

    $rangeMinimum = is_array($priceRange) ? (float) ($priceRange['min'] ?? $price) : $price;
    $rangeMaximum = is_array($priceRange) ? (float) ($priceRange['max'] ?? $price) : $price;
    $hasPriceRange = $rangeMaximum - $rangeMinimum >= 0.01;

    // A single compare-at price cannot accurately describe a range of
    // variant-adjusted prices, so suppress the discount badge in that case.
    $hasDiscount = ! $hasPriceRange
        && $compareAtPrice !== null
        && $compareAtPrice > $price;

    $discountPercent = $hasDiscount
        ? round((($compareAtPrice - $price) / $compareAtPrice) * 100)
        : 0;

    $installmentEnabled =
        $showInstallment
        && ! $hasPriceRange
        && (bool) $product->installment_enabled
        && (int) $product->installment_cash_percent < 100;

    $cashPercent = (int) ($product->installment_cash_percent ?? 100);

    $cashAmount = $installmentEnabled
        ? round($price * ($cashPercent / 100))
        : null;

    $remainderAmount = $installmentEnabled
        ? $price - $cashAmount
        : null;

    $chequeCount = (int) ($product->installment_cheque_count ?? 0);

    $chequeAmount = $installmentEnabled && $chequeCount > 0
        ? round($remainderAmount / $chequeCount)
        : null;
@endphp

<div
    {{ $attributes->merge([
        'class' => 'product-price'
    ]) }}
>
    @if ($hasDiscount)
        <div class="product-price__discount">
            {{ \App\Helpers\PersianNumberHelper::format($discountPercent) }}٪ تخفیف
        </div>
    @endif

    <div
        class="product-price__main"
        @if($priceRange !== null)
            data-product-price-summary
            data-base-price="{{ $price }}"
        @endif
    >
        @if ($hasDiscount)
            <span class="product-price__compare">
                {{ \App\Helpers\PersianNumberHelper::format($compareAtPrice) }}
                <span>تومان</span>
            </span>
        @endif

        <div class="product-price__current">
            <strong data-product-current-price @if($hasPriceRange) hidden @endif>
                {{ \App\Helpers\PersianNumberHelper::format($price) }}
            </strong>

            @if($hasPriceRange)
                <span data-product-price-range class="text-sm font-bold">
                    از {{ \App\Helpers\PersianNumberHelper::format($rangeMinimum) }}
                    تا {{ \App\Helpers\PersianNumberHelper::format($rangeMaximum) }}
                </span>
            @else
                <span data-product-price-range hidden></span>
            @endif

            <span>تومان</span>
        </div>
    </div>

    @if ($installmentEnabled)
        <div class="product-price__installment">
            <div class="product-price__installment-header">
                <span class="product-price__installment-icon">✓</span>
                <span>امکان خرید اقساطی</span>
            </div>

            <div class="product-price__installment-content">
                <div class="product-price__installment-row">
                    <span>پرداخت اولیه</span>
                    <strong>{{ \App\Helpers\PersianNumberHelper::format($cashAmount) }} تومان</strong>
                </div>

                @if ($chequeCount > 0 && $chequeAmount)
                    <div class="product-price__installment-row">
                        <span>{{ $chequeCount }} فقره چک</span>
                        <strong>هر چک {{ \App\Helpers\PersianNumberHelper::format($chequeAmount) }} تومان</strong>
                    </div>
                @endif
            </div>

            <div class="product-price__installment-note">
                {{ $cashPercent }}٪ نقد، مابقی به‌صورت اقساطی
            </div>
        </div>
    @endif
</div>
