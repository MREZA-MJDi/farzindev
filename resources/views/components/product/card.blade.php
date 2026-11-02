@props([
    'product',
    'reveal' => true,
    'variant' => 'default',
    'showAvailability' => true,
])

@php
    $imageModel = $product->images?->first();
    $image = $imageModel?->url;
    $imageAlt = $product->name;
    $imageSrcset = $imageModel?->responsive_srcset;

    $hasDiscount = $product->compare_at_price !== null
        && (float) $product->compare_at_price > (float) $product->price;

    $discountPercent = $hasDiscount
        ? (int) round((((float) $product->compare_at_price - (float) $product->price) / (float) $product->compare_at_price) * 100)
        : 0;

    $installmentEnabled = (bool) $product->installment_enabled;
    $cashPercent = $installmentEnabled ? (int) ($product->installment_cash_percent ?? 0) : 0;
    $chequeCount = $installmentEnabled ? (int) ($product->installment_cheque_count ?? 0) : 0;
    $cashAmount = $installmentEnabled && $cashPercent > 0
        ? round((float) $product->price * ($cashPercent / 100), 2)
        : 0;
    $showInstallment = $cashAmount > 0
        && $cashPercent < 100
        && $product->installment_remainder_method === 'cheque'
        && $chequeCount > 0
        && (int) ($product->installment_interval_months ?? 0) > 0;

    $activeVariants = $product->relationLoaded('variants')
        ? $product->getRelation('variants')
        : $product->variants()->get();
    $hasActiveVariants = $activeVariants->isNotEmpty();
    $isAvailable = $hasActiveVariants
        ? $activeVariants->groupBy('type')->every(
            fn ($options) => $options->contains(fn ($option) => (int) $option->stock > 0)
        )
        : (int) $product->stock > 0;
    $availabilityLabel = $isAvailable
        ? ($hasActiveVariants ? 'موجود در گزینه‌ها' : 'موجود')
        : 'ناموجود';

    $specificationLabels = [
        'width' => 'عرض',
        'installation' => 'نصب',
        'material' => 'جنس',
        'material_type' => 'جنس',
        'color' => 'رنگ',
        'capacity' => 'ظرفیت',
        'type' => 'نوع',
    ];
    $specifications = collect($product->specifications ?? [])
        ->filter(static fn ($value): bool => is_scalar($value) && filled($value))
        ->map(fn ($value, $key): array => [
            'label' => $specificationLabels[$key] ?? (string) $key,
            'value' => trim((string) $value),
        ])
        ->take(3);

@endphp

<article
    data-product-card
    data-product-slug="{{ $product->slug }}"
    class="farzin-product-card group relative flex h-full min-w-0 flex-col {{ $variant === 'round' ? 'farzin-product-card--round-media' : '' }} {{ $variant === 'home-shelf' ? 'farzin-product-card--home-shelf' : '' }} {{ ! $isAvailable ? 'is-unavailable' : '' }}"
    @if($reveal) data-farzin-reveal @endif
>
    <div class="farzin-product-card__media relative overflow-hidden">
        <a
            href="{{ route('product.show', $product->slug) }}"
            class="farzin-product-card__image-link block overflow-hidden {{ $variant === 'round' ? 'farzin-product-card__image-link--round' : '' }}"
            aria-label="مشاهده {{ $product->name }}"
        >
            @if($image)
                <img
                    src="{{ $image }}"
                    @if($imageSrcset) srcset="{{ $imageSrcset }}" sizes="(max-width: 639px) 46vw, (max-width: 1279px) 30vw, 320px" @endif
                    alt="{{ $imageAlt }}"
                    width="720"
                    height="900"
                    loading="lazy"
                    decoding="async"
                    class="farzin-product-card__image h-full w-full object-contain"
                >
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

        @if($showInstallment)
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" width="18" height="18" class="h-[18px] w-[18px]" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"/>
                        </svg>
                    </button>
                </form>
            @endif
        @endauth
    </div>

    <div class="farzin-product-card__content flex min-w-0 flex-1 flex-col px-4 pb-4 pt-3">
        <div class="farzin-product-card__meta flex min-h-5 min-w-0 items-center justify-between gap-2">
            <span class="farzin-product-card__category min-w-0 truncate">
                {{ $product->category?->name ?? 'فرزین' }}
            </span>

        </div>

        <a href="{{ route('product.show', $product->slug) }}" class="mt-2 block min-w-0">
            <h3 class="farzin-product-card__title line-clamp-2 text-sm font-semibold leading-7 sm:text-[15px]">
                {{ $product->name }}
            </h3>
        </a>

        @if($specifications->isNotEmpty())
            <ul class="farzin-product-card__specifications" aria-label="مشخصات کلیدی محصول">
                @foreach($specifications as $specification)
                    <li><span>{{ $specification['label'] }}</span><strong>{{ \Illuminate\Support\Str::limit($specification['value'], 22) }}</strong></li>
                @endforeach
            </ul>
        @endif

        <div class="farzin-product-card__price-row mt-3 flex min-h-10 min-w-0 flex-wrap items-center gap-x-2 gap-y-1">
            <p class="farzin-product-card__price-current">
                {{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}
                <span>تومان</span>
            </p>
            @if($hasDiscount)
                <span class="farzin-product-card__price-old">
                    {{ \App\Helpers\PersianNumberHelper::format((float) $product->compare_at_price) }} تومان
                </span>
            @endif
        </div>

        @if($showAvailability)
            <div class="farzin-product-card__availability {{ $isAvailable ? 'is-available' : 'is-unavailable' }}" aria-label="وضعیت موجودی: {{ $availabilityLabel }}"><span aria-hidden="true"></span>{{ $availabilityLabel }}</div>
        @endif
        <div class="farzin-product-card__installment-slot mt-3">
            @if($showInstallment)
                <div class="farzin-product-card__installment flex w-full items-center justify-between gap-3 rounded-xl p-3">
                    <div class="min-w-0">
                        <p class="text-[10px] text-[var(--livora-stone)]">پیش‌پرداخت</p>
                        <p class="farzin-product-card__installment-value mt-1 text-xs font-semibold text-[var(--livora-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }} تومان
                        </p>
                    </div>
                    <div class="min-w-0 text-left">
                        <p class="text-[10px] text-[var(--livora-stone)]">شرایط</p>
                        <p class="farzin-product-card__installment-value mt-1 text-xs font-semibold text-[var(--livora-accent)]">
                            {{ \App\Helpers\PersianNumberHelper::format($cashPercent) }}٪
                            @if($chequeCount)
                                + {{ \App\Helpers\PersianNumberHelper::format($chequeCount) }} چک
                            @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-auto grid gap-2 pt-3">
            @if($isAvailable && ! $hasActiveVariants)
                <form action="{{ route('cart.add', $product) }}" method="POST">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button
                        type="submit"
                        class="farzin-product-card__cta inline-flex min-h-11 w-full items-center justify-between gap-3"
                        aria-label="افزودن {{ $product->name }} به سبد خرید"
                    >
                        <span>افزودن به سبد</span>
                        <span aria-hidden="true">＋</span>
                    </button>
                </form>
            @elseif($isAvailable)
                <a
                    href="{{ route('product.show', $product->slug) }}"
                    class="farzin-product-card__cta inline-flex min-h-11 w-full items-center justify-between gap-3"
                    aria-label="انتخاب گزینه‌های {{ $product->name }}"
                >
                    <span>انتخاب گزینه‌ها</span>
                    <span aria-hidden="true">←</span>
                </a>
            @else
                <button type="button" disabled class="farzin-product-card__cta inline-flex min-h-11 w-full cursor-not-allowed items-center justify-between gap-3 opacity-60" aria-label="{{ $product->name }} ناموجود است">
                    <span>ناموجود</span>
                </button>
            @endif
            <a
                href="{{ route('product.show', $product->slug) }}"
                class="inline-flex min-h-9 items-center justify-center text-xs font-semibold text-[var(--livora-stone)] transition hover:text-[var(--livora-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--livora-ink)]"
                aria-label="مشاهده مشخصات {{ $product->name }}"
            >مشاهده مشخصات</a>
        </div>
    </div>
</article>
