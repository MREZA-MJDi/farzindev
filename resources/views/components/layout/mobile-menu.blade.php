<div
    x-show="mobileOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] lg:hidden"
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
        data-farzin-mobile-menu-panel
        class="absolute right-0 top-0 flex h-full w-[88%] max-w-md flex-col bg-[var(--livora-cream)] shadow-2xl"
    >

        {{-- Header --}}
        <div class="flex h-[76px] items-center justify-between border-b border-[var(--livora-border)] bg-[var(--livora-white)] px-5">

            <a
                href="{{ route('home') }}"
                @click="mobileOpen = false"
                class="text-xl font-semibold tracking-[0.22em] text-[var(--livora-text)]"
            >
                فرزین
            </a>

            <button
                type="button"
                aria-label="بستن منو"
                @click="mobileOpen = false"
                class="flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-text)] transition hover:bg-[var(--livora-surface)]"
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

            {{-- Main Navigation --}}
            <nav
                aria-label="منوی موبایل"
                class="mt-7"
            >

                <p class="px-2 text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    DISCOVER
                </p>

                <div class="mt-3 divide-y divide-[var(--livora-border)] overflow-hidden rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)]">

                    <a
                        href="{{ route('home') }}"
                        @click="mobileOpen = false"
                        class="group flex min-h-[68px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition hover:bg-[var(--livora-surface)] focus-visible:relative focus-visible:z-10"
                    >
                        <span>خانه</span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-text)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" /></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('shop.index') }}"
                        @click="mobileOpen = false"
                        class="group flex min-h-[68px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition hover:bg-[var(--livora-surface)] focus-visible:relative focus-visible:z-10"
                    >
                        <span>فروشگاه</span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-text)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M3.5 9h17l-1 11h-15l-1-11Z" /><path d="M8 9V6.5a4 4 0 0 1 8 0V9" /></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('categories.index') }}"
                        @click="mobileOpen = false"
                        class="group flex min-h-[68px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition hover:bg-[var(--livora-surface)] focus-visible:relative focus-visible:z-10"
                    >
                        <span>دسته‌بندی‌ها</span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-text)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><rect x="3.5" y="3.5" width="7" height="7" rx="1.3" /><rect x="13.5" y="3.5" width="7" height="7" rx="1.3" /><rect x="3.5" y="13.5" width="7" height="7" rx="1.3" /><rect x="13.5" y="13.5" width="7" height="7" rx="1.3" /></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('blog.index') }}"
                        @click="mobileOpen = false"
                        class="group flex min-h-[68px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition hover:bg-[var(--livora-surface)] focus-visible:relative focus-visible:z-10"
                    >
                        <span>بلاگ و راهنمای خرید</span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-text)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M12 7.5c-2.2-1.6-5-2.1-8.25-1.5v12c3.25-.6 6.05-.1 8.25 1.5m0-12c2.2-1.6 5-2.1 8.25-1.5v12c-3.25-.6-6.05-.1-8.25 1.5" /><path d="M12 7.5v12" /></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('about') }}"
                        @click="mobileOpen = false"
                        class="group flex min-h-[68px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition hover:bg-[var(--livora-surface)] focus-visible:relative focus-visible:z-10"
                    >
                        <span>درباره فرزین</span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-text)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><circle cx="12" cy="12" r="9" /><path d="M12 11v5m0-8h.01" /></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('contact') }}"
                        @click="mobileOpen = false"
                        class="group flex min-h-[68px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition hover:bg-[var(--livora-surface)] focus-visible:relative focus-visible:z-10"
                    >
                        <span>تماس با ما</span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-text)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M5 4.5h3l1.4 3.7-2 1.4a15 15 0 0 0 7 7l1.4-2 3.7 1.4v3c0 .8-.7 1.5-1.5 1.5A14.5 14.5 0 0 1 3.5 6c0-.8.7-1.5 1.5-1.5Z" /></svg>
                        </span>
                    </a>

                </div>

            </nav>


            {{-- Customer --}}
            <div class="mt-8">

                <p class="px-2 text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    FARZIN GUIDE
                </p>

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">

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
                    class="mt-5 inline-flex rounded-2xl bg-[var(--livora-white)] px-5 py-3 text-xs font-medium text-[var(--livora-text)]"
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
                    class="text-xs font-medium text-[var(--livora-text)]"
                >
                    تماس با ما
                </a>

            </div>

        </div>

    </div>

</div>
