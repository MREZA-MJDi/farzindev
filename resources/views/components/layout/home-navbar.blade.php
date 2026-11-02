@props([
    'cartCount' => 0,
    'brandName' => 'فرزین',
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

<nav class="fz-home-header sticky top-0 z-[80]" aria-label="ناوبری اصلی فرزین">
    <div class="fz-home-header__main">
        <div class="fz-home-header__inner">
            <a href="{{ route('home') }}" class="fz-home-header__brand" aria-label="{{ $brandName }}، صفحه اصلی">
                @if($brandLogo)
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}" width="150" height="48">
                @else
                    <span>{{ $brandName }}</span>
                @endif
                <small>تجهیزات آشپزخانه</small>
            </a>

            <form action="{{ route('shop.index') }}" method="GET" role="search" aria-label="جستجوی محصولات فرزین" class="fz-home-header__search">
                <label class="sr-only" for="fz-home-search">جستجوی هود، سینک یا محصول</label>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.3 4.3"/></svg>
                <input
                    id="fz-home-search"
                    type="search"
                    name="search"
                    value="{{ request('search', '') }}"
                    autocomplete="off"
                    enterkeyhint="search"
                    placeholder="نام هود، سینک یا محصول موردنظرت را جستجو کن..."
                >
                <button type="submit">جستجو</button>
            </form>

            <div class="fz-home-header__actions">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a class="fz-home-header__account" href="{{ route('admin.dashboard') }}">مدیریت</a>
                    @else
                        <a class="fz-home-header__account" href="{{ route('account.index') }}">حساب من</a>
                    @endif
                @else
                    <a class="fz-home-header__account" href="{{ route('login') }}">ورود / ثبت‌نام</a>
                @endauth

                <a href="{{ route('cart.index') }}" class="fz-home-header__cart" aria-label="سبد خرید، {{ \App\Helpers\PersianNumberHelper::format($cartCount) }} کالا">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 11.2h10.9l2-7.5H6"/><circle cx="9" cy="19" r="1.4"/><circle cx="17" cy="19" r="1.4"/></svg>
                    <span>سبد خرید</span>
                    <b data-cart-count aria-live="polite" aria-atomic="true">{{ \App\Helpers\PersianNumberHelper::format($cartCount) }}</b>
                </a>

                <button
                    type="button"
                    class="fz-home-header__menu"
                    aria-label="باز کردن منو"
                    aria-controls="farzin-mobile-menu"
                    :aria-expanded="mobileOpen.toString()"
                    @click="searchOpen = false; mobileOpen = true"
                >
                    <svg x-show="!mobileOpen" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    <svg x-show="mobileOpen" x-cloak viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>
    </div>

    @if($navCategories->isNotEmpty())
        <div class="fz-home-header__categories">
            <div class="fz-home-header__categories-inner" aria-label="دسته‌بندی‌های فعال">
                @foreach($navCategories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" @if(request()->routeIs('categories.show') && request()->route('slug') === $category->slug) aria-current="page" @endif>
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</nav>
