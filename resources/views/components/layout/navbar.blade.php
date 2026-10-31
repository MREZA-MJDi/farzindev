@props(['cartCount' => 0])

<nav
    x-data
    aria-label="ناوبری اصلی فرزین"
    class="farzin-site-header sticky top-0 z-50 w-full border-b border-[var(--livora-border)] bg-[var(--livora-ink)]/95 text-[var(--livora-text)] backdrop-blur-xl"
>
    <x-layout.container>

        <div class="flex min-h-[68px] items-center justify-between gap-3 py-3 lg:h-[76px] lg:gap-6 lg:py-0">

            {{-- =========================================================
                BRAND
            ========================================================== --}}
            <div class="flex items-center">

                <a
                    href="{{ route('home') }}"
                    class="group inline-flex items-center"
                    aria-label="فرزین"
                >
                    <span class="farzin-wordmark" aria-hidden="true">
                        <span class="farzin-wordmark__main">فرزین</span>
                    </span>
                </a>

            </div>


            {{-- =========================================================
                DESKTOP NAVIGATION
            ========================================================== --}}
            <div class="hidden min-w-0 items-center gap-3 lg:flex xl:gap-8">

                <a
                    href="{{ route('home') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-text)]' => request()->routeIs('home'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-text)]' => !request()->routeIs('home'),
                    ])
                >
                    خانه

                    @if(request()->routeIs('home'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('shop.index') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-text)]' => request()->routeIs('shop.index', 'product.show'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-text)]' => !request()->routeIs('shop.index', 'product.show'),
                    ])
                >
                    فروشگاه

                    @if(request()->routeIs('shop.index', 'product.show'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('categories.index') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-text)]' => request()->routeIs('categories.*'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-text)]' => !request()->routeIs('categories.*'),
                    ])
                >
                    دسته‌بندی‌ها

                    @if(request()->routeIs('categories.*'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('blog.index') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-text)]' => request()->routeIs('blog.*'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-text)]' => !request()->routeIs('blog.*'),
                    ])
                >
                    بلاگ

                    @if(request()->routeIs('blog.*'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('about') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-text)]' => request()->routeIs('about'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-text)]' => !request()->routeIs('about'),
                    ])
                >
                    درباره ما

                    @if(request()->routeIs('about'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('contact') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-text)]' => request()->routeIs('contact'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-text)]' => !request()->routeIs('contact'),
                    ])
                >
                    تماس

                    @if(request()->routeIs('contact'))
                        <span class="absolute inset-x-0 -bottom-1 mx-auto h-px w-5 bg-[var(--livora-accent)]"></span>
                    @endif
                </a>

            </div>


            {{-- =========================================================
                RIGHT ACTIONS
            ========================================================== --}}
            <div class="flex min-w-0 items-center gap-1">

                {{-- Compact desktop search for the real Farzin catalogue --}}
                <form
                    action="{{ route('shop.index') }}"
                    method="GET"
                    role="search"
                    aria-label="جستجوی محصولات فرزین"
                    class="hidden xl:block xl:w-[clamp(13rem,18vw,17rem)]"
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
                            placeholder="جستجوی محصول یا دسته..."
                            class="w-full min-w-0 rounded-full border border-[var(--livora-border)] bg-[var(--livora-white)] py-2.5 pl-10 pr-4 text-xs text-[var(--livora-text)] placeholder:text-[var(--livora-stone)] transition focus:border-[var(--livora-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--livora-accent)]/20"
                        >
                        <button
                            type="submit"
                            aria-label="جستجو"
                            title="جستجو"
                            class="absolute left-1 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-[var(--livora-stone)] transition hover:bg-[var(--livora-surface)] hover:text-[var(--livora-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[var(--livora-accent)]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                                <circle cx="11" cy="11" r="6.5"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16 16 4.2 4.2"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Mobile menu --}}
                <button
                    type="button"
                    aria-label="باز کردن منو"
                    :aria-expanded="mobileOpen.toString()"
                    @click="searchOpen = false; mobileOpen = true"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[var(--livora-text)] transition hover:bg-[var(--livora-white)] lg:hidden"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                {{-- Search --}}
                <button
                    type="button"
                    aria-label="جستجو"
                    :aria-expanded="searchOpen.toString()"
                    @click="openSearch($event.currentTarget)"
                    class="group hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-text)] transition duration-300 hover:bg-[var(--livora-white)] lg:flex xl:hidden"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-[19px] w-[19px] transition duration-300 group-hover:scale-105"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                        />
                    </svg>
                </button>


                {{-- Wishlist --}}
                @auth

                    @if(auth()->user()->isCustomer())

                        <a
                            href="{{ route('account.wishlist.index') }}"
                            aria-label="علاقه‌مندی‌ها"
                            @class([
                                'group hidden h-10 w-10 items-center justify-center rounded-full transition duration-300 lg:flex',
                                'bg-[var(--livora-white)] text-[var(--livora-accent)]' => request()->routeIs('account.wishlist.*'),
                                'text-[var(--livora-text)] hover:bg-[var(--livora-white)]' => !request()->routeIs('account.wishlist.*'),
                            ])
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-[19px] w-[19px] transition duration-300 group-hover:scale-105"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"
                                />
                            </svg>
                        </a>

                    @else

                        <a
                            href="{{ route('login') }}"
                            aria-label="ورود برای مشاهده علاقه‌مندی‌ها"
                            class="group hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-text)] transition duration-300 hover:bg-[var(--livora-white)] lg:flex"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-[19px] w-[19px] transition duration-300 group-hover:scale-105"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"
                                />
                            </svg>
                        </a>

                    @endif

                @else

                    <a
                        href="{{ route('login') }}"
                        aria-label="ورود برای مشاهده علاقه‌مندی‌ها"
                        class="group hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-text)] transition duration-300 hover:bg-[var(--livora-white)] lg:flex"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-[19px] w-[19px] transition duration-300 group-hover:scale-105"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"
                            />
                        </svg>
                    </a>

                @endauth
                {{-- =====================================================
                    CART
                ====================================================== --}}
                <a
                    href="{{ route('cart.index') }}"
                    aria-label="سبد خرید"
                    class="group relative hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-text)] transition duration-300 hover:bg-[var(--livora-white)] lg:flex"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-[19px] w-[19px] transition duration-300 group-hover:scale-105"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.836L5.75 7.5h13.5l1.125-4.5M5.75 7.5l1.5 6h9.5l1.5-6M8.25 19.5a.75.75 0 1 1-1.5 0Zm9.75 0a.75.75 0 1 1-1.5 0Z"
                        />
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


                {{-- =====================================================
                    ACCOUNT
                ====================================================== --}}
                @auth

                    @if(auth()->user()->isAdmin())

                        <a
                            href="{{ route('admin.dashboard') }}"
                            aria-label="پنل مدیریت"
                            class="group hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-text)] transition duration-300 hover:bg-[var(--livora-white)] lg:flex"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-[19px] w-[19px] transition duration-300 group-hover:scale-105"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0A3.75 3.75 0 0 1 15.75 6ZM4.5 20.25a8.25 8.25 0 0 1 15 0"
                                />
                            </svg>
                        </a>

                    @else

                        <a
                            href="{{ route('account.index') }}"
                            aria-label="حساب کاربری"
                            @class([
                                'group hidden h-10 w-10 items-center justify-center rounded-full transition duration-300 lg:flex',
                                'bg-[var(--livora-white)] text-[var(--livora-accent)]' => request()->routeIs('account.*'),
                                'text-[var(--livora-text)] hover:bg-[var(--livora-white)]' => !request()->routeIs('account.*'),
                            ])
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-[19px] w-[19px] transition duration-300 group-hover:scale-105"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0A3.75 3.75 0 0 1 15.75 6ZM4.5 20.25a8.25 8.25 0 0 1 15 0"
                                />
                            </svg>
                        </a>

                    @endif

                @else

                    <a
                        href="{{ route('login') }}"
                        aria-label="حساب کاربری"
                        class="group hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-text)] transition duration-300 hover:bg-[var(--livora-white)] lg:flex"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-[19px] w-[19px] transition duration-300 group-hover:scale-105"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0A3.75 3.75 0 0 1 15.75 6ZM4.5 20.25a8.25 8.25 0 0 1 15 0"
                            />
                        </svg>
                    </a>

                @endauth


            </div>

        </div>

    </x-layout.container>

</nav>
