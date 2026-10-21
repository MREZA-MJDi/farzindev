@props(['product'])

@php
    $image = $product->images->first(fn ($candidate) => filled($candidate->url));
@endphp

<article class="fz-product-card fz-reveal">
    <div
        class="fz-product-card__image {{ $image ? '' : 'fz-product-card__image--fallback' }}"
        aria-hidden="true"
    >
        @if($image)
            <img
                src="{{ $image->url }}"
                alt=""
                width="720"
                height="900"
                loading="lazy"
                decoding="async"
            >
        @else
            <span class="fz-product-card__fallback-mark">FZ</span>
            <span class="fz-product-card__fallback-label">تصویر محصول ثبت نشده</span>
        @endif

        @if($product->is_new)
            <span class="fz-product-card__badge">تازه</span>
        @endif
        <span class="fz-product-card__open">↗</span>
    </div>

    <div class="fz-product-card__body">
        <div class="fz-product-card__meta">
            <span>{{ $product->category?->name ?? 'فرزین' }}</span>
            <span class="fz-product-card__sku">{{ $product->sku }}</span>
        </div>

        <h3>{{ $product->name }}</h3>

        <div class="fz-product-card__bottom">
            <p class="fz-product-card__price">
                <strong>{{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}</strong>
                <span>تومان</span>
            </p>
            <span class="fz-product-card__arrow" aria-hidden="true">←</span>
        </div>
    </div>

    <a
        class="fz-product-card__hit-area"
        href="{{ route('product.show', $product->slug) }}"
        aria-label="مشاهدهٔ {{ $product->name }}"
    ></a>
</article>
