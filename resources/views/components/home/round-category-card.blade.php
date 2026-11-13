@props(['category'])

<a class="fz-home-round-category" href="{{ route('categories.show', $category->slug) }}" aria-label="مشاهده دسته {{ $category->name }}">
    <span class="fz-home-round-category__image">
        <img
            src="{{ $category->home_category_image_url }}"
            alt="{{ $category->name }}"
            width="640"
            height="480"
            loading="lazy"
            decoding="async"
        >
    </span>
    <span class="fz-home-round-category__body">
        <strong>{{ $category->name }}</strong>
    </span>
</a>
