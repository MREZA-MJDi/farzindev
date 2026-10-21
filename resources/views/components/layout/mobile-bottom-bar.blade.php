<nav
    class="farzin-mobile-nav fixed inset-x-3 bottom-3 z-[90] lg:hidden"
    aria-label="ناوبری موبایل"
>
    <div class="farzin-mobile-nav__inner mx-auto grid w-full max-w-md grid-cols-5 items-center gap-1 rounded-[1.35rem] border border-[var(--livora-border)] bg-[color:var(--livora-cream)]/95 p-1.5 shadow-[0_16px_45px_rgba(24,23,21,.14)] backdrop-blur-2xl">

        <a href="{{ route('home') }}" class="flex min-w-0 flex-col items-center justify-center gap-1.5 rounded-xl px-1 py-2.5 text-[var(--livora-stone)] transition hover:text-[var(--livora-ink)]" aria-label="خانه">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>
            </svg>
            <span class="whitespace-nowrap text-[9px] font-medium">خانه</span>
        </a>

        <a href="{{ route('shop.index') }}" class="flex flex-col items-center justify-center gap-1.5 rounded-2xl px-2 py-2.5 text-[var(--livora-stone)] transition hover:text-[var(--livora-ink)]" aria-label="فروشگاه">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 8h12l1.5 12h-15zM8 8a4 4 0 0 1 8 0"/>
            </svg>
            <span class="text-[9px] font-medium">فروشگاه</span>
        </a>

        <button type="button"
            @click="searchOpen = true; mobileOpen = false"
            class="flex flex-col items-center justify-center gap-1.5 rounded-2xl px-2 py-2.5 text-[var(--livora-stone)] transition hover:text-[var(--livora-ink)]"
            aria-label="جستجو"
            :aria-expanded="searchOpen.toString()"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="11" cy="11" r="6.5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m16 16 4.2 4.2"/>
            </svg>
            <span class="text-[9px] font-medium">جستجو</span>
        </button>

        <a href="{{ route('cart.index') }}" class="relative flex min-w-0 flex-col items-center justify-center gap-1.5 rounded-xl px-1 py-2.5 text-[var(--livora-stone)] transition hover:text-[var(--livora-ink)]" aria-label="سبد خرید">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h1.5l1.5 10h11l2-7H6M9 19.5a1 1 0 1 1-2 0m10 0a1 1 0 1 1-2 0"/>
            </svg>
            @php
                $cartCount = 0;
                if (auth()->check() && auth()->user()->isCustomer()) {
                    $cart = auth()->user()->activeCart;
                    if ($cart) {
                        $cartCount = $cart->itemCount();
                    }
                }
            @endphp
            @if($cartCount > 0)
                <span class="absolute right-2 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-[var(--livora-accent)] px-1 text-[8px] font-semibold text-white">{{ $cartCount }}</span>
            @endif
            <span class="text-[9px] font-medium">سبد</span>
        </a>

        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 flex-col items-center justify-center gap-1.5 rounded-xl px-1 py-2.5 text-[var(--livora-ink)] transition" aria-label="پنل مدیریت">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 20 7v5c0 4.5-3.3 7.4-8 9-4.7-1.6-8-4.5-8-9V7z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M12 9v6"/>
                    </svg>
                    <span class="text-[9px] font-medium">مدیریت</span>
                </a>
            @elseif(auth()->user()->isCustomer())
                <a href="{{ route('account.index') }}" class="flex flex-col items-center justify-center gap-1.5 rounded-2xl px-2 py-2.5 text-[var(--livora-stone)] transition hover:text-[var(--livora-ink)]" aria-label="حساب کاربری">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <circle cx="12" cy="8" r="3.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/>
                    </svg>
                    <span class="text-[9px] font-medium">حساب</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex flex-col items-center justify-center gap-1.5 rounded-2xl px-2 py-2.5 text-[var(--livora-stone)] transition hover:text-[var(--livora-ink)]" aria-label="ورود">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <circle cx="12" cy="8" r="3.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/>
                    </svg>
                    <span class="text-[9px] font-medium">ورود</span>
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="flex flex-col items-center justify-center gap-1.5 rounded-2xl px-2 py-2.5 text-[var(--livora-stone)] transition hover:text-[var(--livora-ink)]" aria-label="ورود">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="12" cy="8" r="3.5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/>
                </svg>
                <span class="text-[9px] font-medium">ورود</span>
            </a>
        @endauth

    </div>
</nav>