@props(['cartCount' => 0, 'brandName' => 'فرجی‌هوم', 'brandLogo' => null])

<nav
    aria-label="ناوبری اصلی {{ $brandName }}"
    class="farzin-site-header sticky top-0 z-[80] w-full border-b border-[var(--livora-border)] bg-white text-[var(--livora-text)]"
>
    <x-layout.container>
        <div class="flex min-h-[70px] items-center justify-between gap-3 py-3 lg:min-h-[78px] lg:gap-6 lg:py-0">

            {{-- Brand --}}
            <x-layout.header-brand :brand-name="$brandName" :brand-logo="$brandLogo" />

            {{-- Desktop navigation --}}
            <div class="farzin-site-header__nav hidden min-w-0 flex-1 items-center justify-center gap-1 lg:flex xl:gap-2">
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
                    aria-label="جستجوی محصولات فرجی‌هوم"
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
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" width="16" height="16" class="h-4 w-4" aria-hidden="true">
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
                    :class="{ 'is-active': searchOpen }"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" width="19" height="19" class="h-[19px] w-[19px]" aria-hidden="true">
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
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" width="19" height="19" class="h-[19px] w-[19px]" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 8.8c0 5.3-8.8 10.1-8.8 10.1S3.2 14.1 3.2 8.8a4.8 4.8 0 0 1 8.8-2.6 4.8 4.8 0 0 1 8.8 2.6Z"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" aria-label="ورود برای مشاهده علاقه‌مندی‌ها" title="علاقه‌مندی‌ها" class="farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" width="19" height="19" class="h-[19px] w-[19px]" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 8.8c0 5.3-8.8 10.1-8.8 10.1S3.2 14.1 3.2 8.8a4.8 4.8 0 0 1 8.8-2.6 4.8 4.8 0 0 1 8.8 2.6Z"/>
                            </svg>
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" aria-label="ورود برای مشاهده علاقه‌مندی‌ها" title="علاقه‌مندی‌ها" class="farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" width="19" height="19" class="h-[19px] w-[19px]" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 8.8c0 5.3-8.8 10.1-8.8 10.1S3.2 14.1 3.2 8.8a4.8 4.8 0 0 1 8.8-2.6 4.8 4.8 0 0 1 8.8 2.6Z"/>
                        </svg>
                    </a>
                @endauth

                {{-- Cart --}}
                <x-layout.header-cart-link :cart-count="$cartCount" />

                {{-- Account --}}
                <x-layout.header-account-link />

                                {{-- Mobile menu trigger --}}
                <x-layout.header-menu-button />

            </div>
        </div>
    </x-layout.container>
</nav>
