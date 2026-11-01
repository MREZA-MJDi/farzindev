@props(['cartCount' => 0])

<nav
    aria-label="ناوبری اصلی فرزین"
    class="farzin-site-header sticky top-0 z-[80] w-full border-b border-[var(--livora-border)] bg-white text-[var(--livora-text)]"
>
    <x-layout.container>
        <div class="flex min-h-[70px] items-center justify-between gap-3 py-3 lg:min-h-[78px] lg:gap-6 lg:py-0">

            {{-- Brand --}}
            <a
                href="{{ route('home') }}"
                class="farzin-site-header__brand group inline-flex min-w-0 shrink-0 items-center"
                aria-label="فرزین، صفحه اصلی"
            >
                <span class="farzin-wordmark">
                    <span class="farzin-wordmark__main">فرزین</span>
                    <span class="farzin-wordmark__caption">تجهیزات مدرن آشپزخانه</span>
                </span>
            </a>

            {{-- Desktop navigation --}}
            <div class="hidden min-w-0 flex-1 items-center justify-center gap-1 lg:flex xl:gap-2">
                <a
                    href="{{ route('home') }}"
                    @class(['farzin-site-header__nav-link', 'is-active' => request()->routeIs('home')])
                    @if(request()->routeIs('home')) aria-current="page" @endif
                >خانه</a>

                <a
                    href="{{ route('shop.index') }}"
                    @class(['farzin-site-header__nav-link', 'is-active' => request()->routeIs('shop.index', 'product.show')])
                    @if(request()->routeIs('shop.index', 'product.show')) aria-current="page" @endif
                >فروشگاه</a>

                <a
                    href="{{ route('categories.index') }}"
                    @class(['farzin-site-header__nav-link', 'is-active' => request()->routeIs('categories.*')])
                    @if(request()->routeIs('categories.*')) aria-current="page" @endif
                >دسته‌بندی‌ها</a>

                <a
                    href="{{ route('blog.index') }}"
                    @class(['farzin-site-header__nav-link', 'is-active' => request()->routeIs('blog.*')])
                    @if(request()->routeIs('blog.*')) aria-current="page" @endif
                >بلاگ</a>

                <a
                    href="{{ route('about') }}"
                    @class(['farzin-site-header__nav-link', 'is-active' => request()->routeIs('about')])
                    @if(request()->routeIs('about')) aria-current="page" @endif
                >درباره ما</a>

                <a
                    href="{{ route('contact') }}"
                    @class(['farzin-site-header__nav-link', 'is-active' => request()->routeIs('contact')])
                    @if(request()->routeIs('contact')) aria-current="page" @endif
                >تماس</a>
            </div>

            {{-- Search, customer actions and mobile menu --}}
            <div class="flex shrink-0 items-center gap-1 sm:gap-1.5">

                {{-- Full search field on wide screens --}}
                <form
                    action="{{ route('shop.index') }}"
                    method="GET"
                    role="search"
                    aria-label="جستجوی محصولات فرزین"
                    class="hidden xl:block xl:w-[clamp(12rem,17vw,16rem)]"
                >
                    <label for="navbar-product-search" class="sr-only">جستجوی هود، سینک یا دسته‌بندی</label>
                    <div class="relative">
                        <input
                            id="navbar-product-search"
                            name="search"
                            type="search"
                            value="{{ request('search', '') }}"
                            autocomplete="off"
                            enterkeyhint="search"
                            placeholder="جستجوی محصول..."
                            class="farzin-site-header__search-input w-full rounded-full border border-[var(--livora-border)] bg-[var(--livora-cream)] py-2.5 pl-10 pr-4 text-xs text-[var(--livora-text)] placeholder:text-[var(--livora-stone)] transition focus:border-[var(--livora-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--livora-accent)]/15"
                        >
                        <button
                            type="submit"
                            aria-label="جستجو"
                            title="جستجو"
                            class="absolute left-1 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-[var(--livora-stone)] transition hover:bg-white hover:text-[var(--livora-accent)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[var(--livora-accent)]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                                <circle cx="11" cy="11" r="6.5"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16 16 4.2 4.2"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Search icon for tablet and compact desktop --}}
                <button
                    type="button"
                    aria-label="جستجوی محصولات"
                    :aria-expanded="searchOpen.toString()"
                    @click="openSearch($event.currentTarget)"
                    class="farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex xl:hidden"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-[19px] w-[19px]" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16 16 4.2 4.2"/>
                    </svg>
                </button>

                {{-- Wishlist --}}
                @auth
                    @if(auth()->user()->isCustomer())
                        <a
                            href="{{ route('account.wishlist.index') }}"
                            aria-label="علاقه‌مندی‌ها"
                            title="علاقه‌مندی‌ها"
                            @class(['farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex', 'is-active' => request()->routeIs('account.wishlist.*')])
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-[19px] w-[19px]" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 8.8c0 5.3-8.8 10.1-8.8 10.1S3.2 14.1 3.2 8.8a4.8 4.8 0 0 1 8.8-2.6 4.8 4.8 0 0 1 8.8 2.6Z"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" aria-label="ورود برای مشاهده علاقه‌مندی‌ها" title="علاقه‌مندی‌ها" class="farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-[19px] w-[19px]" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 8.8c0 5.3-8.8 10.1-8.8 10.1S3.2 14.1 3.2 8.8a4.8 4.8 0 0 1 8.8-2.6 4.8 4.8 0 0 1 8.8 2.6Z"/>
                            </svg>
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" aria-label="ورود برای مشاهده علاقه‌مندی‌ها" title="علاقه‌مندی‌ها" class="farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-[19px] w-[19px]" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 8.8c0 5.3-8.8 10.1-8.8 10.1S3.2 14.1 3.2 8.8a4.8 4.8 0 0 1 8.8-2.6 4.8 4.8 0 0 1 8.8 2.6Z"/>
                        </svg>
                    </a>
                @endauth

                {{-- Cart --}}
                <a
                    href="{{ route('cart.index') }}"
                    aria-label="سبد خرید"
                    title="سبد خرید"
                    class="farzin-site-header__action group relative hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-[19px] w-[19px] transition group-hover:scale-105" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3.75h1.6c.5 0 .94.35 1.06.84L6.3 7.5h13.1l-1.6 7.1H7.5L6.3 7.5M8.5 19.2a.9.9 0 1 1-1.8 0 .9.9 0 0 1 1.8 0Zm9.1 0a.9.9 0 1 1-1.8 0 .9.9 0 0 1 1.8 0Z"/>
                    </svg>
                    <span
                        data-cart-count
                        @class([
                            'absolute -right-0.5 -top-0.5 flex h-[17px] min-w-[17px] items-center justify-center rounded-full bg-[var(--livora-accent)] px-1 text-[9px] font-semibold leading-none text-white',
                            'hidden' => $cartCount < 1,
                        ])
                        aria-live="polite"
                        aria-atomic="true"
                    >{{ $cartCount }}</span>
                </a>

                {{-- Account --}}
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" aria-label="پنل مدیریت" title="پنل مدیریت" class="farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex">
                    @else
                        <a href="{{ route('account.index') }}" aria-label="حساب کاربری" title="حساب کاربری" @class(['farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex', 'is-active' => request()->routeIs('account.*')])>
                    @endif
                @else
                    <a href="{{ route('login') }}" aria-label="ورود / ثبت‌نام" title="ورود / ثبت‌نام" class="farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex">
                @endauth
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-[19px] w-[19px]" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/>
                    </svg>
                </a>

                {{-- Mobile menu trigger --}}
                <button
                    type="button"
                    aria-label="باز کردن منوی اصلی"
                    aria-controls="farzin-mobile-menu"
                    :aria-expanded="mobileOpen.toString()"
                    @click="searchOpen = false; mobileOpen = true"
                    class="farzin-site-header__action flex h-10 w-10 shrink-0 items-center justify-center rounded-full transition lg:hidden"
                >
                    <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <svg x-show="mobileOpen" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18"/>
                    </svg>
                </button>

            </div>
        </div>
    </x-layout.container>
</nav>
