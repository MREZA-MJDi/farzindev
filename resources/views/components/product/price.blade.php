@props([
'product',
'showInstallment' => true,
'compact' => false,
])

@php
    $price = (float) $product->price;
    $compareAtPrice = $product->compare_at_price
        ? (float) $product->compare_at_price
        : null;

    $hasDiscount = $compareAtPrice !== null
        && $compareAtPrice > $price;

    $discountPercent = $hasDiscount
        ? round((($compareAtPrice - $price) / $compareAtPrice) * 100)
        : 0;

    $installmentEnabled =
        $showInstallment
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
    {{-- Discount badge --}}
    @if ($hasDiscount)
        <div class="product-price__discount">
            {{ $discountPercent }}٪ تخفیف
        </div>
    @endif

    {{-- Main price --}}
    <div class="product-price__main">

        @if ($hasDiscount)
            <span class="product-price__compare">
                {{ \App\Helpers\PersianNumberHelper::format($compareAtPrice) }}
                <span>تومان</span>
            </span>
        @endif

        <div class="product-price__current">
            <strong>
                {{ \App\Helpers\PersianNumberHelper::format($price) }}
            </strong>

            <span>
                تومان
            </span>
        </div>

    </div>

    {{-- Installment information --}}
    @if ($installmentEnabled)

        <div class="product-price__installment">

            <div class="product-price__installment-header">
                <span class="product-price__installment-icon">
                    ✓
                </span>

                <span>
                    امکان خرید اقساطی
                </span>
            </div>

            <div class="product-price__installment-content">

                <div class="product-price__installment-row">
                    <span>
                        پرداخت اولیه
                    </span>

                    <strong>
                        {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }}
                        تومان
                    </strong>
                </div>

                @if ($chequeCount > 0 && $chequeAmount)
                    <div class="product-price__installment-row">
                        <span>
                            {{ $chequeCount }} فقره چک
                        </span>

                        <strong>
                            هر چک
                            {{ \App\Helpers\PersianNumberHelper::format($chequeAmount) }}
                            تومان
                        </strong>
                    </div>
                @endif

            </div>

            <div class="product-price__installment-note">
                {{ $cashPercent }}٪ نقد،
                مابقی به‌صورت اقساطی
            </div>

        </div>

    @endif

</div>
