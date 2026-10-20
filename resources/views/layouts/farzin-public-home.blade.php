<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#171a1b">
    <title>@yield('title', 'فرزین فرجی | هود و سینک آشپزخانه')</title>
    <meta name="description" content="@yield('description', 'فرزین فرجی؛ هود و سینک آشپزخانه و راهنمای انتخاب متناسب با فضای شما.')">
    <link rel="canonical" href="@yield('canonical', url('/'))">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="فرزین">
    <meta property="og:title" content="@yield('title', 'فرزین فرجی | هود و سینک آشپزخانه')">
    <meta property="og:description" content="@yield('description', 'فرزین فرجی؛ هود و سینک آشپزخانه و راهنمای انتخاب متناسب با فضای شما.')">
    <meta property="og:url" content="{{ url('/') }}">
    @vite(['resources/css/farzin-home.css', 'resources/js/farzin-home.js'])
    @stack('head')
</head>
<body class="fz-public-home-shell">
    <a class="fz-skip-link" href="#fz-main">رفتن به محتوای اصلی</a>

    <header class="fz-header" data-fz-header>
        <div class="fz-header__inner">
            <a class="fz-brand" href="{{ route('home') }}" aria-label="فرزین، صفحه اصلی">
                <span class="fz-brand__mark" aria-hidden="true">ف</span>
                <span class="fz-brand__word">
                    <strong>فرزین</strong>
                    <small>هود و سینک آشپزخانه</small>
                </span>
            </a>

            <nav class="fz-nav" aria-label="ناوبری اصلی">
                <a href="{{ route('home') }}" aria-current="page">خانه</a>
                <a href="{{ route('shop.index') }}">فروشگاه</a>
                <a href="{{ route('about') }}">درباره ما</a>
                <a href="{{ route('contact') }}">تماس با ما</a>
            </nav>

            <div class="fz-header__actions">
                <a class="fz-header__cart" href="{{ route('cart.index') }}" aria-label="سبد خرید">
                    <svg viewBox="0 0 24 24" aria-hidden="true" fill="none">
                        <path d="M3.5 4.5h2l2.1 10.1a1.7 1.7 0 0 0 1.7 1.35h7.9a1.7 1.7 0 0 0 1.65-1.3L20.5 8H6.1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="9.5" cy="19.2" r="1.1" fill="currentColor"/>
                        <circle cx="17.2" cy="19.2" r="1.1" fill="currentColor"/>
                    </svg>
                </a>

                @auth
                    @if(auth()->user()->isCustomer())
                        <a class="fz-header__account" href="{{ route('account.index') }}">حساب من</a>
                    @elseif(auth()->user()->isAdmin())
                        <a class="fz-header__account" href="{{ route('admin.dashboard') }}">مدیریت</a>
                    @endif
                @else
                    <a class="fz-header__account" href="{{ route('login') }}">ورود</a>
                @endauth

                <button
                    class="fz-menu-toggle"
                    type="button"
                    data-fz-menu-toggle
                    aria-controls="fz-mobile-nav"
                    aria-expanded="false"
                    aria-label="باز کردن منو"
                >
                    <span></span><span></span>
                </button>
            </div>
        </div>

        <nav class="fz-mobile-nav" id="fz-mobile-nav" data-fz-mobile-nav aria-label="ناوبری موبایل" hidden>
            <a href="{{ route('home') }}">خانه</a>
            <a href="{{ route('shop.index') }}">فروشگاه</a>
            <a href="{{ route('about') }}">درباره ما</a>
            <a href="{{ route('contact') }}">تماس با ما</a>
        </nav>
    </header>

    <main id="fz-main">
        @yield('content')
    </main>

    <footer class="fz-footer">
        <div class="fz-footer__main">
            <div class="fz-footer__brand">
                <a class="fz-brand fz-brand--footer" href="{{ route('home') }}">
                    <span class="fz-brand__mark" aria-hidden="true">ف</span>
                    <span class="fz-brand__word">
                        <strong>فرزین</strong>
                        <small>هود و سینک آشپزخانه</small>
                    </span>
                </a>
                <p>انتخابی آگاهانه برای آشپزخانه‌ای که هر روز در آن زندگی می‌کنیم؛ با توجه به ابعاد، کارایی، جنس و جزئیات نصب.</p>
            </div>

            <div class="fz-footer__links">
                <span class="fz-footer__label">مسیرهای سریع</span>
                <a href="{{ route('shop.index') }}">همه محصولات</a>
                <a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a>
                <a href="{{ route('about') }}">آشنایی با فرزین</a>
                <a href="{{ route('contact') }}">تماس با ما</a>
            </div>

            <div class="fz-footer__links">
                <span class="fz-footer__label">راهنمای انتخاب</span>
                <a href="{{ route('blog.index') }}">راهنمای خرید و مقالات</a>
                <a href="{{ route('contact') }}">مشاوره و پیگیری</a>
                <a href="{{ route('cart.index') }}">سبد خرید</a>
            </div>
        </div>

        <div class="fz-footer__bottom">
            <span>© {{ date('Y') }} فرزین فرجی. تمامی حقوق محفوظ است.</span>
            <a href="{{ route('home') }}">بازگشت به ابتدای صفحه ↑</a>
        </div>
    </footer>
</body>
</html>
