@props([
    'brandName' => 'فرجی‌هوم',
    'brandLogo' => null,
    'variant' => 'default',
])

@if($variant === 'home')
    <a
        href="{{ route('home') }}"
        class="fz-home-header__brand"
        aria-label="{{ $brandName }}، صفحه اصلی"
        data-farzin-header-brand
    >
        @if($brandLogo)
            <img src="{{ $brandLogo }}" alt="{{ $brandName }}" width="150" height="48">
        @else
            <span>{{ $brandName }}</span>
        @endif
        <small>تجهیزات آشپزخانه</small>
    </a>
@else
    <a
        href="{{ route('home') }}"
        class="farzin-site-header__brand group inline-flex min-w-0 shrink-0 items-center"
        aria-label="{{ $brandName }}، صفحه اصلی"
        data-farzin-header-brand
    >
        <span class="farzin-wordmark">
            @if($brandLogo)
                <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="max-h-11 max-w-36 object-contain">
            @else
                <span class="farzin-wordmark__main">{{ $brandName }}</span>
            @endif
            <span class="farzin-wordmark__caption">تجهیزات آشپزخانه</span>
        </span>
    </a>
@endif
