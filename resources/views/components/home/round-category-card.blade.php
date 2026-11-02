@props(['category'])

<a class="fz-home-round-category" href="{{ route('categories.show', $category->slug) }}" aria-label="مشاهده دسته {{ $category->name }}">
    <span class="fz-home-round-category__image">
        <img
            src="{{ $category->home_category_image_url }}"
            @if($category->home_category_image_srcset) srcset="{{ $category->home_category_image_srcset }}" sizes="(max-width: 639px) 76px, 92px" @endif
            alt="{{ $category->name }}"
            width="180"
            height="180"
            loading="lazy"
            decoding="async"
        >
    </span>
    <strong>{{ $category->name }}</strong>
    <small>{{ \App\Helpers\PersianNumberHelper::format($category->products_count) }} محصول</small>
</a>
