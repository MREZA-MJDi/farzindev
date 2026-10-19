<div
    x-show="mobileOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[60] lg:hidden"
>
    {{-- Backdrop --}}
    <button
        type="button"
        aria-label="بستن منو"
        @click="mobileOpen = false"
        class="absolute inset-0 bg-black/30 backdrop-blur-sm"
    ></button>

    {{-- Panel --}}
    <div
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 flex h-full w-[88%] max-w-md flex-col bg-[var(--livora-cream)] shadow-2xl"
    >

        {{-- Header --}}
        <div class="flex h-[76px] items-center justify-between border-b border-[var(--livora-border)] bg-[var(--livora-white)] px-5">

            <a
                href="{{ route('home') }}"
                @click="mobileOpen = false"
                class="text-xl font-semibold tracking-[0.22em] text-[var(--livora-ink)]"
            >
                فرزین
            </a>

            <button
                type="button"
                aria-label="بستن منو"
                @click="mobileOpen = false"
                class="flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition hover:bg-[var(--livora-surface)]"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18 18 6M6 6l12 12"
                    />
                </svg>
            </button>

        </div>


        {{-- Content --}}
        <div class="flex-1 overflow-y-auto px-5 py-6">

            {{-- Search --}}
            <button
                type="button"
                @click="
                    mobileOpen = false;
                    searchOpen = true;

                    $nextTick(() => {
                        $refs.searchInput?.focus();
                    });
                "
                class="flex w-full items-center gap-3 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-4 text-right"
            >

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0 text-[var(--livora-stone)]"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                    />
                </svg>

                <span class="text-sm text-[var(--livora-stone)]">
                    جستجوی محصول، دسته‌بندی...
                </span>

            </button>


            {{-- Main Navigation --}}
            <nav
                aria-label="منوی موبایل"
                class="mt-7"
            >

                <p class="px-2 text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    DISCOVER
                </p>

                <div class="mt-3 divide-y divide-[var(--livora-border)] rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)]">

                    <a
                        href="{{ route('home') }}"
                        @click="mobileOpen = false"
                        class="flex items-center justify-between px-4 py-4 text-sm font-medium"
                    >
                        <span>خانه</span>
                        <span class="text-[var(--livora-stone)]">←</span>
                    </a>

                    <a
                        href="{{ route('shop.index') }}"
                        @click="mobileOpen = false"
                        class="flex items-center justify-between px-4 py-4 text-sm font-medium"
                    >
                        <span>فروشگاه</span>
                        <span class="text-[var(--livora-stone)]">←</span>
                    </a>

                    <a
                        href="{{ route('categories.index') }}"
                        @click="mobileOpen = false"
                        class="flex items-center justify-between px-4 py-4 text-sm font-medium"
                    >
                        <span>دسته‌بندی‌ها</span>
                        <span class="text-[var(--livora-stone)]">←</span>
                    </a>

                    <a
                        href="{{ route('blog.index') }}"
                        @click="mobileOpen = false"
                        class="flex items-center justify-between px-4 py-4 text-sm font-medium"
                    >
                        <span>بلاگ و راهنمای خرید</span>
                        <span class="text-[var(--livora-stone)]">←</span>
                    </a>

                    <a
                        href="{{ route('about') }}"
                        @click="mobileOpen = false"
                        class="flex items-center justify-between px-4 py-4 text-sm font-medium"
                    >
                        <span>درباره فرزین</span>
                        <span class="text-[var(--livora-stone)]">←</span>
                    </a>

                    <a
                        href="{{ route('contact') }}"
                        @click="mobileOpen = false"
                        class="flex items-center justify-between px-4 py-4 text-sm font-medium"
                    >
                        <span>تماس با ما</span>
                        <span class="text-[var(--livora-stone)]">←</span>
                    </a>

                </div>

            </nav>


            {{-- Customer --}}
            <div class="mt-8">

                <p class="px-2 text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    FARZIN GUIDE
                </p>

                <div class="mt-3 grid grid-cols-2 gap-3">

                    <a
                        href="{{ route('cart.index') }}"
                        @click="mobileOpen = false"
                        class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4"
                    >

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                            01
                        </div>

                        <p class="mt-4 text-sm font-semibold">
                            سبد خرید
                        </p>

                        <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                            محصولات انتخاب‌شده
                        </p>

                    </a>


                    @auth

                        @if(auth()->user()->isCustomer())

                            <a
                                href="{{ route('account.index') }}"
                                @click="mobileOpen = false"
                                class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4"
                            >

                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                    02
                                </div>

                                <p class="mt-4 text-sm font-semibold">
                                    حساب من
                                </p>

                                <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                    سفارش‌ها و علاقه‌مندی‌ها
                                </p>

                            </a>

                        @else

                            <a
                                href="{{ route('admin.dashboard') }}"
                                @click="mobileOpen = false"
                                class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4"
                            >

                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                    02
                                </div>

                                <p class="mt-4 text-sm font-semibold">
                                    پنل مدیریت
                                </p>

                                <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                    مدیریت فروشگاه
                                </p>

                            </a>

                        @endif

                    @else

                        <a
                            href="{{ route('login') }}"
                            @click="mobileOpen = false"
                            class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4"
                        >

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                02
                            </div>

                            <p class="mt-4 text-sm font-semibold">
                                ورود
                            </p>

                            <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                ورود به حساب
                            </p>

                        </a>

                    @endauth

                </div>

            </div>


            {{-- Installment Highlight --}}
            <div class="mt-8 rounded-3xl bg-[var(--livora-ink)] p-5 text-white">

                <p class="text-[10px] uppercase tracking-[0.18em] text-white/40">
                    FARZIN BUYING GUIDE
                </p>

                <h2 class="mt-3 text-lg font-semibold">
                    برای انتخاب هود و سینک، اول مشخصات را دقیق بررسی کن.
                </h2>

                <p class="mt-2 text-xs leading-7 text-white/50">
                    ابعاد، نوع نصب و ویژگی‌های محصول را با نیاز آشپزخانه‌ات تطبیق بده.
                </p>

                <a
                    href="{{ route('shop.index') }}"
                    @click="mobileOpen = false"
                    class="mt-5 inline-flex rounded-2xl bg-white px-5 py-3 text-xs font-medium text-[var(--livora-ink)]"
                >
                    مشاهده محصولات
                </a>

            </div>

        </div>


        {{-- Footer --}}
        <div class="border-t border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

            <div class="flex items-center justify-between gap-4">

                <div>

                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                        فرزین
                    </p>

                    <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                        Kitchen Appliances
                    </p>

                </div>

                <a
                    href="{{ route('contact') }}"
                    @click="mobileOpen = false"
                    class="text-xs font-medium text-[var(--livora-ink)]"
                >
                    تماس با ما
                </a>

            </div>

        </div>

    </div>

</div>
