@props(['product'])

@php
    $image = $product->images->first(fn ($candidate) => filled($candidate->url));
    $hasVariants = $product->variants->isNotEmpty();
    $canAddDirectly = ! $hasVariants && (int) $product->stock > 0;
@endphp

<article class="fz-product-card fz-reveal" data-product-card>
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
        <span class="fz-product-card__open" aria-hidden="true">↗</span>
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

            @if($canAddDirectly)
                <form
                    class="fz-product-card__cart-form"
                    action="{{ route('cart.add', $product) }}"
                    method="POST"
                    data-cart-add-form
                >
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button
                        class="fz-product-card__cart-button"
                        type="submit"
                        data-cart-fly-button
                        aria-label="افزودن {{ $product->name }} به سبد خرید"
                        title="افزودن به سبد خرید"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h1.5l1.7 10h11l2-7H6M9 19.5a1 1 0 1 1-2 0m10 0a1 1 0 1 1-2 0"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m-2-2h4"/>
                        </svg>
                    </button>
                    <span class="fz-product-card__cart-message" data-cart-message role="status" aria-live="polite"></span>
                </form>
            @elseif($hasVariants)
                <a
                    class="fz-product-card__cart-action"
                    href="{{ route('product.show', $product->slug) }}"
                    aria-label="انتخاب ویژگی‌های {{ $product->name }}"
                    title="انتخاب ویژگی‌ها"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M7 7h10v10"/>
                    </svg>
                </a>
            @else
                <span class="fz-product-card__out-of-stock" aria-label="ناموجود">ناموجود</span>
            @endif
        </div>
    </div>

    <a
        class="fz-product-card__hit-area"
        href="{{ route('product.show', $product->slug) }}"
        aria-label="مشاهدهٔ {{ $product->name }}"
    ></a>
</article>
