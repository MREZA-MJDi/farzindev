@props([
    'category',
    'index' => 0,
])

<a
    class="fz-category-card fz-category-card--editorial fz-reveal"
    style="--fz-category-delay: {{ max(0, (int) $index) * 90 }}ms"
    href="{{ route('categories.show', $category) }}"
    aria-label="مشاهده دسته‌بندی {{ $category->name }}"
>
    <span class="fz-category-card__art">
        @if($category->homepage_image_url)
            <img
                class="fz-category-card__image"
                src="{{ $category->homepage_image_url }}"
                @if($category->homepage_image_srcset) srcset="{{ $category->homepage_image_srcset }}" sizes="(max-width: 639px) 88vw, (max-width: 1023px) 42vw, (max-width: 1279px) 28vw, 260px" @endif
                alt="{{ $category->name }}"
                width="640"
                height="480"
                loading="lazy"
                decoding="async"
            >
        @else
            <span class="fz-category-card__monogram" aria-hidden="true">{{ $category->slug === 'sinks' ? 'S' : 'H' }}</span>
        @endif
        @if($category->hoverMedia?->is_animated_preview)
            @if($category->hoverMedia->is_video)
                <video
                    data-farzin-category-video
                    data-farzin-video-src="{{ $category->hoverMedia->url }}"
                    muted
                    loop
                    playsinline
                    preload="none"
                    aria-hidden="true"
                ></video>
            @elseif($category->hoverMedia->mime_type === 'image/gif')
                <img
                    data-farzin-category-gif
                    data-farzin-gif-src="{{ $category->hoverMedia->url }}"
                    alt=""
                    width="640"
                    height="480"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                >
            @endif
        @endif
        <span class="fz-category-card__image-halo" aria-hidden="true"></span>
    </span>

    <span class="fz-category-card__content">
        <span class="fz-category-card__eyebrow">دسته محصولات</span>
        <strong>{{ $category->name }}</strong>
        <small>{{ \App\Helpers\PersianNumberHelper::format($category->products_count) }} محصول فعال</small>
        <span class="fz-category-card__link">
            مشاهده محصولات
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5m7 7-7-7 7-7"/>
            </svg>
        </span>
    </span>
</a>
