@props([
    'product',
    'reveal' => true,
    'variant' => 'default',
    'showAvailability' => true,
])

@php
    $imageModel = $product->primaryImage();
    $image = $imageModel?->url;
    $imageAlt = $product->name;
    $imageSrcset = $imageModel?->responsive_srcset;

    $hasDiscount = $product->compare_at_price !== null
        && (float) $product->compare_at_price > (float) $product->price;

    $discountPercent = $hasDiscount
        ? (int) round((((float) $product->compare_at_price - (float) $product->price) / (float) $product->compare_at_price) * 100)
        : 0;

    $activeVariants = $product->relationLoaded('variants')
        ? $product->getRelation('variants')
        : $product->variants()->get();
    $hasActiveVariants = $activeVariants->isNotEmpty();
    $isAvailable = \App\Support\ProductPurchaseRules::isAvailable($product, $activeVariants);
    $showInstallment = \App\Support\ProductPurchaseRules::hasValidInstallmentPlan($product, $activeVariants);
    $availabilityLabel = $isAvailable
        ? ($hasActiveVariants ? 'موجود در گزینه‌ها' : 'موجود')
        : 'ناموجود';

    // The homepage shelf shows up to three useful saved specifications.
    // Prefer the structured category attributes and retain legacy JSON values
    // so existing catalogue records do not lose their highlights.
    $cardSpecifications = collect();
    if ($variant === 'home-shelf') {
        $categoryAttributes = $product->category?->relationLoaded('attributes')
            ? $product->category->getRelation('attributes')
            : collect();
        $attributeValues = $product->relationLoaded('attributeValues')
            ? $product->getRelation('attributeValues')
            : collect();

        if ($categoryAttributes->isNotEmpty() && $attributeValues->isNotEmpty()) {
            $cardSpecifications = $categoryAttributes
                ->filter(fn ($attribute) => (bool) $attribute->pivot->is_key_spec)
                ->take(3)
                ->map(function ($attribute) use ($attributeValues): ?string {
                    $value = $attributeValues->where('attribute_id', $attribute->id)
                        ->map(function ($row) use ($attribute) {
                            if ($row->option) return $row->option->label;
                            if ($row->value_boolean !== null) return $row->value_boolean ? 'بله' : 'خیر';
                            if ($row->value_number !== null) {
                                $number = rtrim(rtrim(number_format((float) $row->value_number, 3, '.', ''), '0'), '.');
                                return \\App\\Helpers\\PersianNumberHelper::format($number)
                                    . ($attribute->unit ? ' ' . $attribute->unit : '');
                            }
                            return trim((string) $row->value_text);
                        })
                        ->filter(fn ($item) => $item !== '')
                        ->unique()
                        ->join('، ');

                    return $value !== '' ? $attribute->label . ': ' . $value : null;
                })
                ->filter()
                ->values();
        }

        if ($cardSpecifications->isEmpty() && is_array($product->specifications)) {
            $cardSpecifications = collect($product->specifications)
                ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
                ->map(fn ($value) => trim((string) $value))
                ->take(3)
                ->values();
        }
    }
@endphp

<article
    data-product-card
    data-product-slug="{{ $product->slug }}"
    class="farzin-product-card group relative flex h-full min-w-0 flex-col {{ $variant === 'round' ? 'farzin-product-card--round-media' : '' }} {{ $variant === 'home-shelf' ? 'farzin-product-card--home-shelf' : '' }} {{ $variant === 'catalog' ? 'farzin-product-card--catalog' : '' }} {{ ! $isAvailable ? 'is-unavailable' : '' }}"
    @if($reveal) data-farzin-reveal @endif
>
    <div class="farzin-product-card__media relative overflow-hidden bg-[#eef2f5]">
        <a
            href="{{ route('product.show', $product->slug) }}"
            class="farzin-product-card__image-link block aspect-square overflow-hidden"
            aria-label="مشاهده {{ $product->name }}"
        >
            @if($image)
                <img
                    src="{{ $image }}"
                    @if($imageSrcset) srcset="{{ $imageSrcset }}" sizes="(max-width: 639px) 50vw, (max-width: 1023px) 33vw, 280px" @endif
                    alt="{{ $imageAlt }}"
                    width="800"
                    height="600"
                    loading="lazy"
                    decoding="async"
                    class="farzin-product-card__image h-full w-full object-contain p-3 transition-transform duration-300 group-hover:scale-105"
                >
            @else
                <span class="farzin-product-card__image-fallback" aria-label="تصویر محصول در دسترس نیست">
                    <strong class="text-xs">فرجی هوم</strong>
                    <small>تصویر کالا</small>
                </span>
            @endif
        </a>

        <div class="absolute right-3 top-3 z-10 flex max-w-[calc(100%-5rem)] flex-col items-start gap-1.5">
            @if(! $isAvailable)
                <span class="farzin-product-badge bg-slate-700/90 text-white shadow-sm backdrop-blur-sm">ناموجود</span>
            @elseif($product->is_new)
                <span class="farzin-product-badge farzin-product-badge--light">تازه‌وارد</span>
            @endif
            @if($hasDiscount && $isAvailable)
                <span class="farzin-product-badge farzin-product-badge--accent px-2.5 py-1 text-xs font-black sm:text-sm">
                    {{ \App\Helpers\PersianNumberHelper::format($discountPercent) }}٪ تخفیف
                </span>
            @endif
        </div>

        @if($showInstallment && $isAvailable)
            <span class="farzin-installment-seal" aria-label="این محصول امکان خرید اقساطی دارد">
                <strong>خرید</strong>
                <span>اقساطی</span>
            </span>
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

    <div class="farzin-product-card__content flex min-w-0 flex-1 flex-col px-4 pb-4 pt-3.5">
        <a href="{{ route('product.show', $product->slug) }}" class="block min-w-0">
            <h3 class="farzin-product-card__title min-h-[3rem] line-clamp-2 text-sm font-semibold leading-6 sm:text-[15px]">
                {{ $product->name }}
            </h3>
        </a>

        @if($cardSpecifications->isNotEmpty())
            <ul class="farzin-product-card__specifications mt-2 space-y-1 text-xs leading-5 text-slate-600" aria-label="مشخصات کلیدی محصول">
                @foreach($cardSpecifications as $specification)
                    <li class="line-clamp-1">{{ $specification }}</li>
                @endforeach
            </ul>
        @endif

        <div class="farzin-product-card__price-row mt-2.5 flex min-h-[56px] flex-col justify-end">
            <div class="h-5 leading-none">
                @if($hasDiscount)
                    <span class="farzin-product-card__price-old text-sm font-bold text-slate-400 line-through sm:text-base">
                        {{ \App\Helpers\PersianNumberHelper::format((float) $product->compare_at_price) }}
                    </span>
                @else
                    <span class="invisible select-none text-sm" aria-hidden="true">&nbsp;</span>
                @endif
            </div>
            <p class="farzin-product-card__price-current mt-1 flex items-baseline gap-1.5 text-xl font-black sm:text-2xl">
                <span class="farzin-product-card__price-value">{{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}</span>
                <span class="farzin-product-card__price-currency text-xs font-semibold text-slate-500 sm:text-sm">تومان</span>
            </p>
        </div>

        @if($showAvailability)
            <div class="farzin-product-card__availability {{ $isAvailable ? 'is-available' : 'is-unavailable' }} mt-2 flex h-5 items-center gap-1.5 text-xs font-medium {{ $isAvailable ? 'text-emerald-700' : 'text-slate-400' }}" aria-label="وضعیت موجودی: {{ $availabilityLabel }}">
                <span class="farzin-product-card__availability-dot inline-block h-2 w-2 shrink-0 rounded-full {{ $isAvailable ? 'bg-emerald-500' : 'bg-slate-400' }}" aria-hidden="true"></span>
                <span class="farzin-product-card__availability-text whitespace-nowrap">{{ $availabilityLabel }}</span>
            </div>
        @endif

        <div class="mt-auto pt-3">
            @if($isAvailable && ! $hasActiveVariants)
                <form action="{{ route('cart.add', $product) }}" method="POST">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button
                        type="submit"
                        class="farzin-product-card__cta inline-flex min-h-11 w-full items-center justify-between gap-2 px-3 text-xs font-bold sm:text-sm"
                        aria-label="افزودن {{ $product->name }} به سبد خرید"
                    >
                        <span>افزودن به سبد خرید</span>
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.7 2.682-7.1H5.106m0 0L6.75 14.25m-1.644-8.415H21M9 20.25a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm9.75 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                            </svg>
                        </span>
                    </button>
                </form>
            @elseif($isAvailable)
                <a
                    href="{{ route('product.show', $product->slug) }}"
                    class="farzin-product-card__cta inline-flex min-h-11 w-full items-center justify-between gap-2 px-3 text-xs font-bold sm:text-sm"
                    aria-label="انتخاب گزینه‌های {{ $product->name }}"
                >
                    <span>انتخاب گزینه‌ها</span>
                    <span class="farzin-product-card__arrow inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                    </span>
                </a>
            @else
                <button type="button" disabled class="farzin-product-card__cta inline-flex min-h-11 w-full cursor-not-allowed items-center justify-between gap-2 px-3 opacity-60" aria-label="{{ $product->name }} ناموجود است">
                    <span>ناموجود</span>
                    <span class="farzin-product-card__arrow inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold" aria-hidden="true">✕</span>
                </button>
            @endif
        </div>
    </div>
</article>
