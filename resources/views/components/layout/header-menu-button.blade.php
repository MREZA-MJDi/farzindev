@props(['variant' => 'default'])

@if($variant === 'home')
    <button
        type="button"
        class="fz-home-header__menu"
        aria-label="باز کردن منو"
        aria-controls="farzin-mobile-menu"
        :aria-expanded="mobileOpen.toString()"
        @click="searchOpen = false; mobileOpen = true"
        data-farzin-header-menu
    >
        <svg x-show="!mobileOpen" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M3 6h18M3 12h18M3 18h18"/>
        </svg>
        <svg x-show="mobileOpen" x-cloak viewBox="0 0 24 24" aria-hidden="true">
            <path d="m6 6 12 12M18 6 6 18"/>
        </svg>
    </button>
@else
    <button
        type="button"
        aria-label="باز کردن منو"
        aria-controls="farzin-mobile-menu"
        :aria-expanded="mobileOpen.toString()"
        @click="searchOpen = false; mobileOpen = true"
        class="farzin-site-header__action flex h-10 w-10 shrink-0 items-center justify-center rounded-full transition lg:hidden"
        data-farzin-header-menu
    >
        <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" width="24" height="24" class="h-6 w-6" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
        </svg>
        <svg x-show="mobileOpen" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" width="24" height="24" class="h-6 w-6" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18"/>
        </svg>
    </button>
@endif
