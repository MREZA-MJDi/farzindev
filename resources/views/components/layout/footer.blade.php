@props(['contact' => []])
@php
    $footerPhone = \\App\\Support\\StorefrontContact::phone(data_get($contact, 'phone'));
    $footerWhatsapp = \\App\\Support\\StorefrontContact::whatsapp(data_get($contact, 'whatsapp'));
    $footerEmail = trim((string) data_get($contact, 'email', ''));
    $footerAddress = trim((string) data_get($contact, 'address', ''));
    $footerHours = trim((string) data_get($contact, 'hours', ''));
@endphp
<footer class="farzin-site-footer farzin-site-footer--editorial fz-store-footer">
    <x-layout.container class="fz-store-footer__container">
        <div class="fz-store-footer__main">
            <div class="fz-store-footer__brand">
                <a href="{{ route('home') }}" class="fz-store-footer__logo farzin-footer-editorial__logo" aria-label="فرزین، صفحه اصلی">فرزین</a>
                
                <p>کاتالوگ آنلاین هود و سینک آشپزخانه؛ مشخصات و قیمت مدل‌ها را بررسی کنید و انتخابتان را آگاهانه انجام دهید.</p>
                <div class="fz-store-footer__socials" aria-label="شبکه‌های اجتماعی">
                    @if(filled(data_get($contact, 'instagram_url')))<a href="{{ data_get($contact, 'instagram_url') }}" target="_blank" rel="noopener noreferrer" aria-label="اینستاگرام فرزین">اینستاگرام</a>@endif
                    @if(filled(data_get($contact, 'telegram_url')))<a href="{{ data_get($contact, 'telegram_url') }}" target="_blank" rel="noopener noreferrer" aria-label="تلگرام فرزین">تلگرام</a>@endif
                    @if($footerWhatsapp)<a href="https://wa.me/{{ $footerWhatsapp }}" target="_blank" rel="noopener noreferrer" aria-label="واتساپ فرزین">واتساپ</a>@endif
                </div>
            </div>
            <nav class="fz-store-footer__column farzin-footer-editorial__nav" aria-label="پیوندهای اصلی">
                <h2>فروشگاه</h2><a href="{{ route('home') }}">خانه</a><a href="{{ route('shop.index') }}">همه محصولات</a><a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a><a href="{{ route('blog.index') }}">راهنمای خرید و مجله</a><a href="{{ route('about') }}">درباره فرزین</a><a href="{{ route('contact') }}">تماس با ما</a>
            </nav>
            <nav class="fz-store-footer__column" aria-label="خدمات مشتریان">
                <h2>خدمات مشتریان</h2><a href="{{ route('contact') }}">تماس و راهنمایی پیش از خرید</a>
                @auth
                    @if(auth()->user()->isCustomer())<a href="{{ route('account.orders.index') }}">سفارش‌های من</a><a href="{{ route('account.index') }}">حساب کاربری</a>@endif
                @else
                    <a href="{{ route('login', ['intended' => '/account/orders']) }}">ورود و پیگیری سفارش</a>
                @endauth
                <a href="{{ route('terms') }}">قوانین و شرایط</a><a href="{{ route('privacy') }}">حریم خصوصی</a>
            </nav>
            <div class="fz-store-footer__contact farzin-footer-editorial__contact">
                <h2>ارتباط با فرزین</h2>
                @if($footerPhone)<a href="{{ $footerPhone['href'] }}" dir="ltr">{{ $footerPhone['display'] }}</a>@endif
                @if($footerEmail !== '')<a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a>@endif
                @if($footerAddress !== '')<p>{{ $footerAddress }}</p>@endif
                @if($footerHours !== '')<p class="fz-store-footer__hours">ساعات پاسخ‌گویی: {{ $footerHours }}</p>@endif
                @if(! $footerPhone && $footerEmail === '' && $footerAddress === '' && ! $footerWhatsapp)<p>برای ارسال پرسش، از فرم تماس با فرزین استفاده کنید.</p>@endif
                <a class="fz-store-footer__contact-link" href="{{ route('contact') }}">رفتن به صفحه تماس <span aria-hidden="true">←</span></a>
            </div>
        </div>
        <div class="fz-store-footer__bottom"><span>© {{ \App\Helpers\JalaliHelper::format(now(), 'Y') }} فرزین · تمامی حقوق محفوظ است.</span><span>طراحی‌شده برای انتخاب بهتر تجهیزات آشپزخانه</span></div>
    </x-layout.container>
</footer>
