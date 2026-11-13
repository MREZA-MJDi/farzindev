@props([
    'cartCount' => 0,
    'brandName' => 'فرجی‌هوم',
    'brandLogo' => null,
    'categories' => null,
])

@php
    $navCategories = $categories;
    if ($navCategories === null) {
        $navCategories = \App\Models\Category::query()
            ->active()
            ->whereHas('products', function ($products) {
                $products->active()
                    ->where('price', '>', 0)
                    ->whereNotNull('name')
                    ->where('name', '<>', '')
                    ->where('sku', 'not like', 'FZ-SEED-%')
                    ->where('sku', 'not like', 'LIV-%')
                    ->where('sku', 'not like', 'E2E-%')
                    ->whereHas('images');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }
@endphp

<nav class="fz-home-header sticky top-0 z-[80]" aria-label="ناوبری اصلی فرجی‌هوم">
    <div class="fz-home-header__main">
        <div class="fz-home-header__inner">
            <x-layout.header-brand variant="home" :brand-name="$brandName" :brand-logo="$brandLogo" />

            <form action="{{ route('shop.index') }}" method="GET" role="search" aria-label="جستجوی محصولات فرجی‌هوم" class="fz-home-header__search">
                <label class="sr-only" for="fz-home-search">جستجوی هود، سینک یا محصول</label>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.3 4.3"/></svg>
                <input
                    id="fz-home-search"
                    type="search"
                    name="search"
                    value="{{ request('search', '') }}"
                    autocomplete="off"
                    enterkeyhint="search"
                    placeholder="نام هود، اجاق گاز، سینک یا محصول موردنظرت را جستجو کن..."
                >
                <button type="submit">جستجو</button>
            </form>

            <div class="fz-home-header__actions">
                <x-layout.header-account-link variant="home" />

                <x-layout.header-cart-link variant="home" :cart-count="$cartCount" />

                <x-layout.header-menu-button variant="home" />
            </div>
        </div>
    </div>

    <div class="fz-home-header__categories">
        <div class="fz-home-header__categories-inner" aria-label="منوی اصلی و دسته‌بندی‌ها">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>خانه</a>
            <a href="{{ route('shop.index') }}" @if(request()->routeIs('shop.*')) aria-current="page" @endif>فروشگاه</a>
            <a href="{{ route('blog.index') }}" @if(request()->routeIs('blog.*')) aria-current="page" @endif>مجله</a>
            <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>درباره ما</a>
            <a href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>تماس با ما</a>
            @foreach($navCategories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" @if(request()->routeIs('categories.show') && request()->route('slug') === $category->slug) aria-current="page" @endif>
                        {{ $category->name }}
                    </a>
                @endforeach
        </div>
    </div>
</nav>
