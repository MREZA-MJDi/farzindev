@props(['product'])

@php
    $image = $product->primaryImage();
    $imageUrl = $image?->url;
    $imageSrcset = $image?->responsive_srcset;

    $variants = $product->relationLoaded('variants')
        ? $product->getRelation('variants')
        : $product->variants()->get();
    $hasVariants = $variants->isNotEmpty();
    $isAvailable = $hasVariants
        ? $variants->groupBy('type')->every(
            fn ($options) => $options->contains(fn ($option) => (int) $option->stock > 0)
        )
        : (int) $product->stock > 0;

    $specificationLabels = [
        'width' => 'عرض',
        'installation' => 'نصب',
        'material' => 'جنس',
    ];
    $specifications = collect($product->specifications ?? [])
        ->only(array_keys($specificationLabels))
        ->filter(fn ($value) => filled($value))
        ->map(fn ($value, $key) => [
            'label' => $specificationLabels[$key],
            'value' => trim((string) $value),
        ])
        ->take(3);

    $cashPercent = (int) ($product->installment_cash_percent ?? 0);
    $cashAmount = (bool) $product->installment_enabled && $cashPercent > 0
        ? round((float) $product->price * ($cashPercent / 100))
        : 0;
    $showInstallment = $cashAmount > 0
        && $cashPercent < 100
        && $product->installment_remainder_method === 'cheque'
        && (int) ($product->installment_cheque_count ?? 0) > 0
        && (int) ($product->installment_interval_months ?? 0) > 0;
@endphp

<article class="farzin-product-card group fz-home-product-card {{ $isAvailable ? '' : 'is-unavailable' }}" data-farzin-reveal>
    <a class="fz-home-product-card__media" href="{{ route('product.show', $product->slug) }}" aria-label="مشاهده {{ $product->name }}">
        @if($imageUrl)
            <img
                src="{{ $imageUrl }}"
                @if($imageSrcset) srcset="{{ $imageSrcset }}" sizes="(max-width: 767px) 46vw, (max-width: 1279px) 22vw, 300px" @endif
                alt="{{ $image?->alt ?: $product->name }}"
                width="800"
                height="600"
                loading="lazy"
                decoding="async"
            >
        @endif
    </a>

    <div class="fz-home-product-card__body">
        <h3 class="fz-home-product-card__title">
            <a href="{{ route('product.show', $product->slug) }}">{{ \Illuminate\Support\Str::limit($product->name, 58) }}</a>
        </h3>

        @if($specifications->isNotEmpty())
            <ul class="fz-home-product-card__specs" aria-label="مشخصات کلیدی محصول">
                @foreach($specifications as $specification)
                    <li><span>{{ $specification['label'] }}</span>{{ $specification['value'] }}</li>
                @endforeach
            </ul>
        @endif

        <div class="fz-home-product-card__price">
            <strong>{{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}</strong>
            <span>تومان</span>
        </div>

        <div class="fz-home-product-card__availability {{ $isAvailable ? 'is-available' : 'is-unavailable' }}" aria-label="وضعیت موجودی: {{ $isAvailable ? 'موجود' : 'ناموجود' }}">
            <span aria-hidden="true"></span>{{ $isAvailable ? 'موجود' : 'ناموجود' }}
        </div>

        @if($showInstallment)
            <p class="fz-home-product-card__installment">از {{ \App\Helpers\PersianNumberHelper::format($cashAmount) }} تومان پیش‌پرداخت</p>
        @endif

        <div class="fz-home-product-card__actions">
            @if($isAvailable && ! $hasVariants)
                <form action="{{ route('cart.add', $product) }}" method="POST">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" aria-label="افزودن {{ $product->name }} به سبد خرید">افزودن به سبد <span aria-hidden="true">＋</span></button>
                </form>
            @elseif($isAvailable)
                <a href="{{ route('product.show', $product->slug) }}">انتخاب گزینه‌ها <span aria-hidden="true">←</span></a>
            @else
                <button type="button" disabled>ناموجود</button>
            @endif
        </div>
    </div>
</article>
