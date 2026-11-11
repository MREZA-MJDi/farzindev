@props(['variant' => 'default'])

@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
    $accountUrl = $user
        ? ($isAdmin ? route('admin.dashboard') : route('account.index'))
        : route('login');
    $accountLabel = $user
        ? ($isAdmin ? 'مدیریت' : 'حساب من')
        : 'ورود / ثبت‌نام';
@endphp

@if($variant === 'home')
    <a
        class="fz-home-header__account"
        href="{{ $accountUrl }}"
        aria-label="{{ $accountLabel }}"
        data-farzin-header-account
    >{{ $accountLabel }}</a>
@else
    <a
        href="{{ $accountUrl }}"
        aria-label="حساب کاربری"
        title="{{ $user ? ($isAdmin ? 'پنل مدیریت' : 'حساب کاربری') : 'ورود / ثبت‌نام' }}"
        @class([
            'farzin-site-header__action hidden h-10 w-10 items-center justify-center rounded-full transition lg:flex',
            'is-active' => ($user && ! $isAdmin && request()->routeIs('account.*'))
                || (! $user && request()->routeIs('login', 'register')),
        ])
        @if(! $user && request()->routeIs('login', 'register')) aria-current="page" @endif
        data-farzin-header-account
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" width="19" height="19" class="h-[19px] w-[19px]" aria-hidden="true">
            <circle cx="12" cy="8" r="3.5"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/>
        </svg>
    </a>
@endif
