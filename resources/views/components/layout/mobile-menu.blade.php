@props(['brandName' => 'فرزین', 'brandLogo' => null, 'storefrontContact' => []])

<div
    id="farzin-mobile-menu"
    x-show="mobileOpen"
    x-cloak
    x-init="$watch('mobileOpen', (open) => { if (open) $nextTick(() => $refs.mobileMenuClose?.focus()); })"
    @keydown.escape.window="if (mobileOpen) mobileOpen = false"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[130] lg:hidden"
>
    {{-- Backdrop --}}
    <button
        type="button"
        aria-label="بستن منو"
        @click="mobileOpen = false"
        class="absolute inset-0 bg-[var(--livora-ink)]/40 backdrop-blur-sm"
    ></button>

    {{-- Drawer --}}
    <section
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @click.stop
        @keydown.tab="
            const focusables = Array.from($el.querySelectorAll('a[href],button:not([disabled])')).filter((element) => element.getClientRects().length > 0);
            if (!focusables.length) { $event.preventDefault(); return; }
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); }
            else if (!$event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }
        "
        role="dialog"
        aria-modal="true"
        aria-label="منوی اصلی {{ $brandName }}"
        tabindex="-1"
        data-farzin-mobile-menu-panel
        class="absolute right-0 top-0 flex h-full h-[100dvh] min-h-0 w-[min(88%,26rem)] max-w-md flex-col overflow-hidden border-l border-[var(--livora-border)] bg-[var(--livora-cream)] pb-[env(safe-area-inset-bottom)] shadow-2xl"
    >

        {{-- Drawer header --}}
        <div class="flex min-h-[76px] shrink-0 items-center justify-between border-b border-[var(--livora-border)] bg-white px-5 pt-[env(safe-area-inset-top)]">
            <a
                href="{{ route('home') }}"
                @click="mobileOpen = false"
                class="farzin-wordmark"
                aria-label="{{ $brandName }}، صفحه اصلی"
            >
                @if($brandLogo)<img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="max-h-11 max-w-36 object-contain">@else<span class="farzin-wordmark__main">{{ $brandName }}</span>@endif
                <span class="farzin-wordmark__caption">تجهیزات مدرن آشپزخانه</span>
            </a>

            <button
                x-ref="mobileMenuClose"
                type="button"
                aria-label="بستن منو"
                @click="mobileOpen = false"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[var(--livora-text)] transition hover:bg-[var(--livora-surface)] hover:text-[var(--livora-accent)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--livora-accent)]"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5" aria-hidden="true" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>

        {{-- Scrollable drawer content --}}
        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-6">

            <nav aria-label="منوی موبایل">
                <p class="px-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    دسترسی سریع
                </p>

                <div class="mt-3 divide-y divide-[var(--livora-border)] overflow-hidden rounded-3xl border border-[var(--livora-border)] bg-white">
                    <a
                        href="{{ route('home') }}"
                        @click="mobileOpen = false"
                        @class(['farzin-mobile-menu__link group flex min-h-[62px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('home')])
                        @if(request()->routeIs('home')) aria-current="page" @endif
                    >
                        <span>خانه</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-ink)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" width="20" height="20"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('shop.index') }}"
                        @click="mobileOpen = false"
                        @class(['farzin-mobile-menu__link group flex min-h-[62px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('shop.index', 'product.show')])
                        @if(request()->routeIs('shop.index', 'product.show')) aria-current="page" @endif
                    >
                        <span>فروشگاه</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-ink)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" width="20" height="20"><path d="M3.5 9h17l-1 11h-15l-1-11Z"/><path d="M8 9V6.5a4 4 0 0 1 8 0V9"/></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('categories.index') }}"
                        @click="mobileOpen = false"
                        @class(['farzin-mobile-menu__link group flex min-h-[62px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('categories.*')])
                        @if(request()->routeIs('categories.*')) aria-current="page" @endif
                    >
                        <span>دسته‌بندی‌ها</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-ink)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" width="20" height="20"><rect x="3.5" y="3.5" width="7" height="7" rx="1.3"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.3"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.3"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.3"/></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('blog.index') }}"
                        @click="mobileOpen = false"
                        @class(['farzin-mobile-menu__link group flex min-h-[62px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('blog.*')])
                        @if(request()->routeIs('blog.*')) aria-current="page" @endif
                    >
                        <span>بلاگ و راهنمای خرید</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-ink)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" width="20" height="20"><path d="M12 7.5c-2.2-1.6-5-2.1-8.25-1.5v12c3.25-.6 6.05-.1 8.25 1.5m0-12c2.2-1.6 5-2.1 8.25-1.5v12c-3.25-.6-6.05-.1-8.25 1.5"/><path d="M12 7.5v12"/></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('about') }}"
                        @click="mobileOpen = false"
                        @class(['farzin-mobile-menu__link group flex min-h-[62px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('about')])
                        @if(request()->routeIs('about')) aria-current="page" @endif
                    >
                        <span>درباره {{ $brandName }}</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-ink)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" width="20" height="20"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
                        </span>
                    </a>

                    <a
                        href="{{ route('contact') }}"
                        @click="mobileOpen = false"
                        @class(['farzin-mobile-menu__link group flex min-h-[62px] items-center justify-between gap-4 px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('contact')])
                        @if(request()->routeIs('contact')) aria-current="page" @endif
                    >
                        <span>تماس با ما</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-[var(--livora-ink)] transition group-hover:bg-[var(--livora-ink)] group-hover:text-white" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" width="20" height="20"><path d="M5 4.5h3l1.4 3.7-2 1.4a15 15 0 0 0 7 7l1.4-2 3.7 1.4v3c0 .8-.7 1.5-1.5 1.5A14.5 14.5 0 0 1 3.5 6c0-.8.7-1.5 1.5-1.5Z"/></svg>
                        </span>
                    </a>
                </div>
            </nav>

            {{-- Customer shortcuts --}}
            <div class="mt-7">
                <p class="px-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    حساب و خرید
                </p>

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <a
                        href="{{ route('cart.index') }}"
                        @click="mobileOpen = false"
                        class="rounded-2xl border border-[var(--livora-border)] bg-white p-4 transition hover:border-[var(--livora-accent)]"
                    >
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--livora-accent-soft)] text-[var(--livora-accent)]" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3.75h1.6c.5 0 .94.35 1.06.84L6.3 7.5h13.1l-1.6 7.1H7.5L6.3 7.5M8.5 19.2a.9.9 0 1 1-1.8 0 .9.9 0 0 1 1.8 0Zm9.1 0a.9.9 0 1 1-1.8 0 .9.9 0 0 1 1.8 0Z"/></svg>
                        </span>
                        <span class="mt-3 block text-sm font-semibold text-[var(--livora-text)]">سبد خرید</span>
                        <span class="mt-1 block text-[11px] text-[var(--livora-stone)]">محصولات انتخاب‌شده</span>
                    </a>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" @click="mobileOpen = false" class="rounded-2xl border border-[var(--livora-border)] bg-white p-4 transition hover:border-[var(--livora-accent)]">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-[var(--livora-ink)]" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 20 7v5c0 4.5-3.3 7.4-8 9-4.7-1.6-8-4.5-8-9V7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M12 9v6"/></svg>
                                </span>
                                <span class="mt-3 block text-sm font-semibold text-[var(--livora-text)]">پنل مدیریت</span>
                                <span class="mt-1 block text-[11px] text-[var(--livora-stone)]">مدیریت فروشگاه</span>
                            </a>
                        @else
                            <a href="{{ route('account.index') }}" @click="mobileOpen = false" class="rounded-2xl border border-[var(--livora-border)] bg-white p-4 transition hover:border-[var(--livora-accent)]">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-[var(--livora-ink)]" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5" width="20" height="20"><circle cx="12" cy="8" r="3.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/></svg>
                                </span>
                                <span class="mt-3 block text-sm font-semibold text-[var(--livora-text)]">حساب من</span>
                                <span class="mt-1 block text-[11px] text-[var(--livora-stone)]">سفارش‌ها و علاقه‌مندی‌ها</span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" @click="mobileOpen = false" class="rounded-2xl border border-[var(--livora-border)] bg-white p-4 transition hover:border-[var(--livora-accent)]">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-[var(--livora-ink)]" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5" width="20" height="20"><circle cx="12" cy="8" r="3.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/></svg>
                            </span>
                            <span class="mt-3 block text-sm font-semibold text-[var(--livora-text)]">ورود / ثبت‌نام</span>
                            <span class="mt-1 block text-[11px] text-[var(--livora-stone)]">حساب کاربری {{ $brandName }}</span>
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Buying guide --}}
            <div class="mt-7 rounded-3xl bg-[var(--livora-ink)] p-5 text-white">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-white/60">
                    راهنمای خرید {{ $brandName }}
                </p>
                <h2 class="mt-3 text-base font-bold leading-8 text-white">
                    هود و سینک مناسب آشپزخانه‌ات را آگاهانه انتخاب کن.
                </h2>
                <p class="mt-2 text-xs leading-7 text-white/75">
                    ابعاد، نوع نصب، جنس و مشخصات هر محصول را با نیاز آشپزخانه تطبیق بده.
                </p>
                <a
                    href="{{ route('shop.index') }}"
                    @click="mobileOpen = false"
                    class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-white px-5 py-2.5 text-xs font-bold text-[var(--livora-ink)] transition hover:bg-[var(--livora-accent-soft)] hover:text-[var(--livora-accent)]"
                >
                    مشاهده محصولات
                </a>
            </div>
        </div>

        {{-- Drawer footer --}}
        <div class="shrink-0 border-t border-[var(--livora-border)] bg-white px-5 py-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-[var(--livora-ink)]">{{ $brandName }}</p>
                    <p class="mt-0.5 text-[10px] text-[var(--livora-stone)]">تجهیزات مدرن آشپزخانه</p>
                </div>
                <a
                    href="{{ route('contact') }}"
                    @click="mobileOpen = false"
                    class="text-xs font-semibold text-[var(--livora-accent)] transition hover:text-[var(--livora-accent-dark)]"
                >تماس با ما</a>
            </div>
            @if(filled(data_get($storefrontContact, 'phone')) || filled(data_get($storefrontContact, 'whatsapp')) || filled(data_get($storefrontContact, 'instagram_url')) || filled(data_get($storefrontContact, 'telegram_url')))
                <div class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-[var(--livora-border)] pt-3 text-[11px]">
                    @if(filled(data_get($storefrontContact, 'phone')))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) data_get($storefrontContact, 'phone')) }}" dir="ltr" class="font-semibold text-[var(--livora-text)]">{{ data_get($storefrontContact, 'phone') }}</a>
                    @endif
                    @if(filled(data_get($storefrontContact, 'whatsapp')))
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', (string) data_get($storefrontContact, 'whatsapp')) }}" target="_blank" rel="noopener noreferrer" class="text-[var(--livora-text)]">واتساپ</a>
                    @endif
                    @if(filled(data_get($storefrontContact, 'instagram_url')))
                        <a href="{{ data_get($storefrontContact, 'instagram_url') }}" target="_blank" rel="noopener noreferrer" class="text-[var(--livora-text)]">اینستاگرام</a>
                    @endif
                    @if(filled(data_get($storefrontContact, 'telegram_url')))
                        <a href="{{ data_get($storefrontContact, 'telegram_url') }}" target="_blank" rel="noopener noreferrer" class="text-[var(--livora-text)]">تلگرام</a>
                    @endif
                </div>
            @endif
        </div>
    </section>
</div>
