@props(['product'])

@php
    $image = $product->images->first(fn ($candidate) => filled($candidate->url));
@endphp

@if($image)
    <article class="fz-product-card fz-reveal">
        <a class="fz-product-card__image" href="{{ route('product.show', $product->slug) }}" aria-label="مشاهده {{ $product->name }}">
            <img
                src="{{ $image->url }}"
                alt="{{ $image->alt ?: $product->name }}"
                width="720"
                height="900"
                loading="lazy"
                decoding="async"
            >
            @if($product->is_new)
                <span class="fz-product-card__badge">تازه</span>
            @endif
            <span class="fz-product-card__open" aria-hidden="true">↗</span>
        </a>
        <div class="fz-product-card__body">
            <div class="fz-product-card__meta">
                <span>{{ $product->category?->name ?? 'فرزین' }}</span>
                <span class="fz-product-card__sku">{{ $product->sku }}</span>
            </div>
            <h3><a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a></h3>
            <div class="fz-product-card__bottom">
                <p class="fz-product-card__price">
                    <strong>{{ number_format((float) $product->price) }}</strong>
                    <span>تومان</span>
                </p>
                <a class="fz-product-card__arrow" href="{{ route('product.show', $product->slug) }}" aria-label="مشاهده {{ $product->name }}">←</a>
            </div>
        </div>
    </article>
@endif
