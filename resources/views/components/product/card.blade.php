@props([
    'product',
])

@php
    $image = $product->images?->first()?->url;

    $hasDiscount =
        $product->compare_at_price !== null
        && (float) $product->compare_at_price > (float) $product->price;

    $discountPercent = $hasDiscount
        ? round(
            (
                ((float) $product->compare_at_price - (float) $product->price)
                / (float) $product->compare_at_price
            ) * 100
        )
        : 0;

    $installmentEnabled = (bool) $product->installment_enabled;
    $cashPercent = $installmentEnabled ? (int) ($product->installment_cash_percent ?? 0) : 0;
    $chequeCount = $installmentEnabled ? (int) ($product->installment_cheque_count ?? 0) : 0;
    $cashAmount = $installmentEnabled && $cashPercent > 0
        ? round((float) $product->price * ($cashPercent / 100), 2)
        : 0;
@endphp

<article
    class="farzin-product-card group relative flex h-full min-w-0 flex-col"
    data-farzin-reveal
>
    <div class="farzin-product-card__media relative overflow-hidden rounded-[1.25rem]">
        <a
            href="{{ route('product.show', $product->slug) }}"
            class="farzin-product-card__image-link block aspect-[4/5] overflow-hidden"
            aria-label="مشاهده {{ $product->name }}"
        >
            @if($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $product->name }}"
                    width="640"
                    height="480"
                    loading="lazy"
                    decoding="async"
                    class="farzin-product-card__image h-full w-full object-cover"
                >
            @else
                <div class="farzin-product-fallback flex h-full min-h-[210px] w-full flex-col items-center justify-center gap-3">
                    <span class="farzin-product-fallback__monogram" aria-hidden="true">FZ</span>
                    <span class="farzin-product-fallback__label">فرزین / هود و سینک</span>
                    <span class="text-[11px] text-[var(--livora-stone)]">تصویر محصول ثبت نشده</span>
                </div>
            @endif
        </a>

        <div class="absolute left-3 top-3 z-10 flex flex-col items-start gap-2">
            @if($product->is_new)
                <span class="farzin-product-badge farzin-product-badge--light">تازه‌وارد</span>
            @endif

            @if($hasDiscount)
                <span class="farzin-product-badge farzin-product-badge--accent">
                    {{ \App\Helpers\PersianNumberHelper::format($discountPercent) }}٪ تخفیف
                </span>
            @endif
        </div>

        @if($installmentEnabled)
            <div class="absolute right-3 top-3 z-10">
                <span class="farzin-product-badge farzin-product-badge--dark">خرید اقساطی</span>
            </div>
        @endif

        @auth
            @if(auth()->user()->isCustomer())
                <form
                    action="{{ route('account.wishlist.store', $product) }}"
                    method="POST"
                    class="absolute bottom-3 right-3 z-10"
                >
                    @csrf
                    <button
                        type="submit"
                        aria-label="افزودن {{ $product->name }} به علاقه‌مندی‌ها"
                        class="farzin-product-card__wishlist flex h-10 w-10 items-center justify-center rounded-full"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-[18px] w-[18px]" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"/>
                        </svg>
                    </button>
                </form>
            @endif
        @endauth
    </div>

    <div class="flex flex-1 flex-col px-1 pb-1 pt-4">
        <div class="flex min-h-5 items-center gap-2">
            @if($product->category)
                <span class="farzin-product-card__category-dot" aria-hidden="true"></span>
                <p class="line-clamp-1 text-[11px] font-medium text-[var(--livora-stone)]">
                    {{ $product->category->name }}
                </p>
            @endif
        </div>

        <a href="{{ route('product.show', $product->slug) }}" class="mt-2 block">
            <h3 class="farzin-product-card__title line-clamp-2 text-sm font-semibold leading-7 sm:text-[15px]">
                {{ $product->name }}
            </h3>
        </a>

        <div class="mt-3 flex min-h-10 flex-wrap items-center gap-x-2 gap-y-1">
            @if($hasDiscount)
                <span class="text-base font-bold tracking-tight text-[var(--livora-text)]">
                    {{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}
                    <span class="text-[10px] font-normal text-[var(--livora-stone)]">تومان</span>
                </span>
                <span class="text-xs text-[var(--livora-stone)] line-through">
                    {{ \App\Helpers\PersianNumberHelper::format((float) $product->compare_at_price) }}
                </span>
            @else
                <p class="text-base font-bold tracking-tight text-[var(--livora-text)]">
                    {{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}
                    <span class="text-[10px] font-normal text-[var(--livora-stone)]">تومان</span>
                </p>
            @endif
        </div>

        @if($installmentEnabled && $cashAmount > 0)
            <div class="farzin-product-card__installment mt-3 rounded-xl p-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] text-[var(--livora-stone)]">پیش‌پرداخت</p>
                        <p class="mt-1 text-xs font-semibold text-[var(--livora-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }} تومان
                        </p>
                    </div>
                    <div class="text-left">
                        <p class="text-[10px] text-[var(--livora-stone)]">شرایط</p>
                        <p class="mt-1 text-xs font-semibold text-[var(--livora-accent)]">
                            {{ \App\Helpers\PersianNumberHelper::format($cashPercent) }}٪
                            @if($chequeCount)
                                + {{ \App\Helpers\PersianNumberHelper::format($chequeCount) }} چک
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <a
            href="{{ route('product.show', $product->slug) }}"
            class="farzin-product-card__cta mt-auto inline-flex items-center justify-between gap-3"
        >
            <span>بررسی مشخصات</span>
            <span class="farzin-product-card__arrow" aria-hidden="true">↙</span>
        </a>
    </div>
</article>
