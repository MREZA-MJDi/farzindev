@props(['category'])

<a
    class="fz-category-card fz-category-card--editorial fz-reveal"
    href="{{ route('categories.show', $category) }}"
    aria-label="مشاهده محصولات دسته {{ $category->name }}"
>
    <span class="fz-category-card__art">
        @if($category->homepage_image_url)
            <img
                class="fz-category-card__image"
                src="{{ $category->homepage_image_url }}"
                @if($category->homepage_image_srcset) srcset="{{ $category->homepage_image_srcset }}" sizes="(max-width: 639px) 86vw, (max-width: 1023px) 46vw, 380px" @endif
                alt=""
                width="720"
                height="560"
                loading="lazy"
                decoding="async"
            >
        @else
            <span class="fz-category-card__monogram" aria-hidden="true">FZ</span>
        @endif
        <span class="fz-category-card__overlay" aria-hidden="true"></span>
        <span class="fz-category-card__arrow" aria-hidden="true">↙</span>
    </span>
    <span class="fz-category-card__content">
        <span>
            <strong>{{ $category->name }}</strong>
            <small>{{ \App\Helpers\PersianNumberHelper::format($category->products_count) }} محصول فعال</small>
        </span>
        <span class="fz-category-card__discover">مشاهده مدل‌ها <b aria-hidden="true">←</b></span>
    </span>
</a>
