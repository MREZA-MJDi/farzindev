@props(['product'])

@php
    $image = $product->images->first(fn ($candidate) => filled($candidate->url));
    $price = (float) $product->price;
    $compareAtPrice = $product->compare_at_price !== null ? (float) $product->compare_at_price : null;
    $hasDiscount = $compareAtPrice !== null && $compareAtPrice > $price && $compareAtPrice > 0;
    $discountPercent = $hasDiscount ? (int) round((($compareAtPrice - $price) / $compareAtPrice) * 100) : 0;
    $hasVariants = (int) ($product->variants_count ?? 0) > 0;
    $isAvailable = $hasVariants
        ? (int) ($product->active_variants_stock ?? 0) > 0
        : (int) $product->stock > 0;
    $installmentEnabled = (bool) $product->installment_enabled;
    $cashPercent = $installmentEnabled ? (int) ($product->installment_cash_percent ?? 0) : 0;
    $cashAmount = $installmentEnabled && $cashPercent > 0 ? round($price * ($cashPercent / 100)) : 0;
    $chequeCount = $installmentEnabled ? (int) ($product->installment_cheque_count ?? 0) : 0;
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


@endphp

<a class="fz-product-card fz-product-card--clickable fz-reveal" href="{{ route('product.show', $product->slug) }}" aria-label="مشاهده جزئیات {{ $product->name }}">
    <span class="fz-product-card__image {{ $image ? '' : 'fz-product-card__image--fallback' }}">
        @if($image)
            <img
                src="{{ $image->url }}"
                @if($image->responsive_srcset) srcset="{{ $image->responsive_srcset }}" sizes="(max-width: 639px) 46vw, (max-width: 1023px) 30vw, 300px" @endif
                alt="{{ $image->alt ?: $product->name }}"
                width="720"
                height="640"
                loading="lazy"
                decoding="async"
            >
        @else
            <span class="fz-product-card__fallback-mark" aria-hidden="true">FZ</span>
            <span class="fz-product-card__fallback-label">تصویر محصول ثبت نشده</span>
        @endif

        @if($product->is_new || $hasDiscount)
            <span class="fz-product-card__badges">
                @if($product->is_new)
                    <span class="fz-product-card__badge">تازه</span>
                @endif
                @if($hasDiscount)
                    <span class="fz-product-card__badge fz-product-card__badge--discount">
                        {{ \App\Helpers\PersianNumberHelper::format($discountPercent) }}٪ تخفیف
                    </span>
                @endif
            </span>
        @endif
        <span class="fz-product-card__open" aria-hidden="true">↗</span>
    </span>

    <span class="fz-product-card__body">
        <span class="fz-product-card__meta">
            <span>{{ $product->category?->name ?? 'تجهیزات آشپزخانه' }}</span>
            @if(filled($product->sku))
                <span class="fz-product-card__sku">{{ $product->sku }}</span>
            @endif
        </span>

        <strong class="fz-product-card__title">{{ $product->name }}</strong>
        @if($specifications->isNotEmpty())
            <ul class="fz-home-product-card__specs" aria-label="مشخصات کلیدی محصول">
                @foreach($specifications as $specification)
                    <li><span>{{ $specification['label'] }}</span>{{ $specification['value'] }}</li>
                @endforeach
            </ul>
        @endif

        <span class="fz-product-card__availability {{ $isAvailable ? 'is-available' : 'is-unavailable' }}">
            <i aria-hidden="true"></i>
            {{ $isAvailable ? 'موجود' : 'ناموجود' }}
        </span>

        <span class="fz-product-card__purchase">
            <span class="fz-product-card__price-block">
                @if($hasDiscount)
                    <del class="fz-product-card__price-old">{{ \App\Helpers\PersianNumberHelper::format($compareAtPrice) }} تومان</del>
                @endif
                <span class="fz-product-card__price">
                    <strong>{{ \App\Helpers\PersianNumberHelper::format($price) }}</strong>
                    <small>تومان</small>
                </span>
            </span>
            <span class="fz-product-card__cta">بررسی محصول <b aria-hidden="true">←</b></span>
        </span>

        @if($installmentEnabled && $cashAmount > 0)
            <span class="fz-product-card__installment">
                <span>پیش‌پرداخت {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }} تومان</span>
                <strong>{{ \App\Helpers\PersianNumberHelper::format($cashPercent) }}٪ @if($chequeCount) + {{ \App\Helpers\PersianNumberHelper::format($chequeCount) }} چک @endif</strong>
            </span>
        @endif
    </span>
</a>
