<nav
    x-data
    class="sticky top-0 z-50 w-full border-b border-[var(--livora-border)] bg-[var(--livora-cream)]/90 backdrop-blur-xl"
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
            <div class="hidden items-center gap-9 lg:flex">

                <a
                    href="{{ route('home') }}"
                    @class([
                        'relative py-2 text-sm font-medium transition duration-300',
                        'text-[var(--livora-ink)]' => request()->routeIs('home'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('home'),
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
                        'text-[var(--livora-ink)]' => request()->routeIs('shop.index', 'product.show'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('shop.index', 'product.show'),
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
                        'text-[var(--livora-ink)]' => request()->routeIs('categories.*'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('categories.*'),
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
                        'text-[var(--livora-ink)]' => request()->routeIs('blog.*'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('blog.*'),
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
                        'text-[var(--livora-ink)]' => request()->routeIs('about'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('about'),
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
                        'text-[var(--livora-ink)]' => request()->routeIs('contact'),
                        'text-[var(--livora-stone)] hover:text-[var(--livora-ink)]' => !request()->routeIs('contact'),
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
            <div class="flex items-center gap-1">

                {{-- Mobile menu --}}
                <button
                    type="button"
                    aria-label="باز کردن منو"
                    :aria-expanded="mobileOpen.toString()"
                    @click="searchOpen = false; mobileOpen = true"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] lg:hidden"
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
                    @click="
                        mobileOpen = false;
                        searchOpen = true;

                        $nextTick(() => {
                            $refs.searchInput?.focus();
                        });
                    "
                    class="group flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition duration-300 hover:bg-[var(--livora-white)]"
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
                                'group hidden h-10 w-10 items-center justify-center rounded-full transition duration-300 sm:flex',
                                'bg-[var(--livora-white)] text-[var(--livora-accent)]' => request()->routeIs('account.wishlist.*'),
                                'text-[var(--livora-ink)] hover:bg-[var(--livora-white)]' => !request()->routeIs('account.wishlist.*'),
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
                            class="group hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition duration-300 hover:bg-[var(--livora-white)] sm:flex"
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
                        class="group hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition duration-300 hover:bg-[var(--livora-white)] sm:flex"
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
                @php
                    $cartCount = 0;

                    if (
                        auth()->check()
                        && auth()->user()->isCustomer()
                    ) {
                        $cart = auth()->user()->activeCart;

                        if ($cart) {
                            $cartCount = $cart->itemCount();
                        }
                    }
                @endphp

                <a
                    href="{{ route('cart.index') }}"
                    aria-label="سبد خرید"
                    class="group relative flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition duration-300 hover:bg-[var(--livora-white)]"
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

                    @if($cartCount > 0)

                        <span
                            class="absolute -right-0.5 -top-0.5 flex h-[17px] min-w-[17px] items-center justify-center rounded-full bg-[var(--livora-accent)] px-1 text-[9px] font-semibold leading-none text-white"
                        >
                            {{ $cartCount }}
                        </span>

                    @endif

                </a>


                {{-- =====================================================
                    ACCOUNT
                ====================================================== --}}
                @auth

                    @if(auth()->user()->isAdmin())

                        <a
                            href="{{ route('admin.dashboard') }}"
                            aria-label="پنل مدیریت"
                            class="group hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition duration-300 hover:bg-[var(--livora-white)] sm:flex"
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
                                'group hidden h-10 w-10 items-center justify-center rounded-full transition duration-300 sm:flex',
                                'bg-[var(--livora-white)] text-[var(--livora-accent)]' => request()->routeIs('account.*'),
                                'text-[var(--livora-ink)] hover:bg-[var(--livora-white)]' => !request()->routeIs('account.*'),
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
                        aria-label="ورود"
                        class="group hidden h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition duration-300 hover:bg-[var(--livora-white)] sm:flex"
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
