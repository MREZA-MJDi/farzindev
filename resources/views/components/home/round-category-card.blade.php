@props(['category'])

<a class="fz-home-category-card" href="{{ route('categories.show', $category->slug) }}" aria-label="مشاهده دسته {{ $category->name }}">
    <span class="fz-home-category-card__image">
        <img
            src="{{ $category->home_category_image_url }}"
            alt="{{ $category->name }}"
            width="640"
            height="480"
            loading="lazy"
            decoding="async"
        >
    </span>
    <span class="fz-home-category-card__body">
        <strong>{{ $category->name }}</strong>
        <small>{{ \App\Helpers\PersianNumberHelper::format($category->products_count) }} محصول</small>
        <span class="fz-home-category-card__arrow" aria-hidden="true">←</span>
    </span>
</a>
