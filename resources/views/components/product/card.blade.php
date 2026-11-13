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

    $specifications = collect();
    $categoryAttributes = $product->category?->relationLoaded('attributes')
        ? $product->category->getRelation('attributes')
        : collect();
    $attributeValues = $product->relationLoaded('attributeValues')
        ? $product->getRelation('attributeValues')
        : collect();

    if ($categoryAttributes->isNotEmpty() && $attributeValues->isNotEmpty()) {
        $specifications = $categoryAttributes
            ->filter(fn ($attribute) => (bool) $attribute->pivot->is_key_spec)
            ->take(3)
            ->map(function ($attribute) use ($attributeValues): ?array {
                $value = $attributeValues->where('attribute_id', $attribute->id)
                    ->map(function ($row) use ($attribute) {
                        if ($row->option) return $row->option->label;
                        if ($row->value_boolean !== null) return $row->value_boolean ? 'بله' : 'خیر';
                        if ($row->value_number !== null) {
                            $number = rtrim(rtrim(number_format((float) $row->value_number, 3, '.', ''), '0'), '.');
                            return \App\Helpers\PersianNumberHelper::format($number) . ($attribute->unit ? ' ' . $attribute->unit : '');
                        }
                        return trim((string) $row->value_text);
                    })
                    ->filter(fn ($item) => $item !== '')
                    ->unique()
                    ->join('، ');
                return $value !== '' ? ['label' => $attribute->label, 'value' => $value] : null;
            })
            ->filter()
            ->values();
    }

    if ($specifications->isEmpty()) {
        $specificationLabels = ['width'=>'عرض','installation'=>'نصب','material'=>'جنس','material_type'=>'جنس','color'=>'رنگ','capacity'=>'ظرفیت','type'=>'نوع'];
        $specifications = collect($product->specifications ?? [])
            ->filter(static fn ($value): bool => is_scalar($value) && filled($value))
            ->map(fn ($value, $key): array => ['label'=>$specificationLabels[$key] ?? (string)$key,'value'=>trim((string)$value)])
            ->take(3);
    }
@endphp

<article
    data-product-card
    data-product-slug="{{ $product->slug }}"
    class="farzin-product-card group relative flex h-full min-w-0 flex-col {{ $variant === 'round' ? 'farzin-product-card--round-media' : '' }} {{ $variant === 'home-shelf' ? 'farzin-product-card--home-shelf' : '' }} {{ $variant === 'catalog' ? 'farzin-product-card--catalog' : '' }} {{ ! $isAvailable ? 'is-unavailable' : '' }}"
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
                    @if($imageSrcset) srcset="{{ $imageSrcset }}" sizes="(max-width: 639px) 50vw, (max-width: 1023px) 33vw, 280px" @endif
                    alt="{{ $imageAlt }}"
                    width="800"
                    height="600"
                    loading="lazy"
                    decoding="async"
                    class="farzin-product-card__image h-full w-full object-contain"
                >
            @else
                <span class="farzin-product-card__image-fallback" aria-label="تصویر محصول در دسترس نیست">
                    <strong>FZ</strong>
                    <small>تصویر محصول</small>
                </span>
            @endif
        </a>

        <div class="absolute right-3 top-3 z-10 flex max-w-[calc(100%-5rem)] flex-col items-start gap-1.5">
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

    <div class="farzin-product-card__content flex min-w-0 flex-1 flex-col px-4 pb-4 pt-3">
        <div class="farzin-product-card__meta flex min-h-5 min-w-0 items-center justify-between gap-2">
            <span class="farzin-product-card__category min-w-0 truncate">
                {{ $product->category?->name ?? 'تجهیزات آشپزخانه' }}
            </span>

        </div>

        <a href="{{ route('product.show', $product->slug) }}" class="mt-2 block min-w-0">
            <h3 class="farzin-product-card__title line-clamp-2 text-sm font-semibold leading-7 sm:text-[15px]">
                {{ $product->name }}
            </h3>
        </a>

        @if($variant === 'catalog')
            {{-- Reserve equal specification space so missing specs never shift prices or actions. --}}
            <div class="farzin-product-card__catalog-specs">
                @if($specifications->isNotEmpty())
                    <ul class="farzin-product-card__specifications" aria-label="مشخصات کلیدی محصول">
                        @foreach($specifications as $specification)
                            <li><span>{{ $specification['label'] }}</span><strong>{{ \Illuminate\Support\Str::limit($specification['value'], 22) }}</strong></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @elseif($specifications->isNotEmpty())
            <ul class="farzin-product-card__specifications" aria-label="مشخصات کلیدی محصول">
                @foreach($specifications as $specification)
                    <li><span>{{ $specification['label'] }}</span><strong>{{ \Illuminate\Support\Str::limit($specification['value'], 18) }}</strong></li>
                @endforeach
            </ul>
        @endif

        <div class="farzin-product-card__price-row mt-2 flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1">
            <p class="farzin-product-card__price-current">
                {{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}
                <span>تومان</span>
            </p>
            @if($hasDiscount)
                <span class="farzin-product-card__discount-percent">{{ \App\Helpers\PersianNumberHelper::format($discountPercent) }}٪ تخفیف</span>
                <span class="farzin-product-card__price-old">
                    {{ \App\Helpers\PersianNumberHelper::format((float) $product->compare_at_price) }} تومان
                </span>
            @endif
        </div>

        @if($showAvailability && $isAvailable)
            <div class="farzin-product-card__availability {{ $isAvailable ? 'is-available' : 'is-unavailable' }}" aria-label="وضعیت موجودی: {{ $availabilityLabel }}"><span aria-hidden="true"></span>{{ $availabilityLabel }}</div>
        @endif
        <div class="mt-auto grid gap-1.5 pt-2">
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
