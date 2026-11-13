@props(['product'])

@php
    $image = $product->primaryImage();
@endphp

<aside class="fz-home-hero-offer" aria-label="پیشنهاد فرجی‌هوم، محصول موجود">
    <span class="fz-home-hero-offer__eyebrow">پیشنهاد فرجی‌هوم</span>
    @if($image?->url)
        <img
            src="{{ $image->url }}"
            alt="{{ $image->alt ?: $product->name }}"
            width="160"
            height="120"
            loading="lazy"
            decoding="async"
        >
    @endif
    <div class="fz-home-hero-offer__details">
        <h2>{{ \Illuminate\Support\Str::limit($product->name, 48) }}</h2>
        <strong>{{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }} <small>تومان</small></strong>
        <span class="fz-home-hero-offer__stock"><i aria-hidden="true"></i> موجود و آماده سفارش</span>
    </div>
    <a href="{{ route('product.show', $product->slug) }}" aria-label="مشاهده پیشنهاد فرجی‌هوم: {{ $product->name }}">
        مشاهده <span aria-hidden="true">←</span>
    </a>
</aside>
