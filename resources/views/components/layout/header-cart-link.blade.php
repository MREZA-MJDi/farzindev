@props(['cartCount' => 0, 'variant' => 'default'])

@if($variant === 'home')
    <a
        href="{{ route('cart.index') }}"
        class="fz-home-header__cart"
        aria-label="سبد خرید، {{ \App\Helpers\PersianNumberHelper::format($cartCount) }} کالا"
        data-farzin-header-cart
    >
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M3 4h2l2.2 11.2h10.9l2-7.5H6"/>
            <circle cx="9" cy="19" r="1.4"/>
            <circle cx="17" cy="19" r="1.4"/>
        </svg>
        <span>سبد خرید</span>
        <b data-cart-count aria-live="polite" aria-atomic="true">{{ \App\Helpers\PersianNumberHelper::format($cartCount) }}</b>
    </a>
@else
    <a
        href="{{ route('cart.index') }}"
        aria-label="سبد خرید"
        title="سبد خرید"
        @class([
            'farzin-site-header__action group relative hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex',
            'is-active' => request()->routeIs('cart.*', 'checkout.*'),
        ])
        data-farzin-header-cart
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" width="19" height="19" class="h-[19px] w-[19px] transition group-hover:scale-105" aria-hidden="true">
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
@endif
