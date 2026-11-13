@props(['contact' => [], 'settings' => [], 'brandLogoUrl' => null, 'brandName' => 'فرجی‌هوم'])
@php
    $footerPhone = \App\Support\StorefrontContact::phone(data_get($contact, 'phone'));
    $footerWhatsapp = \App\Support\StorefrontContact::whatsapp(data_get($contact, 'whatsapp'));
    $footerEmail = trim((string) data_get($contact, 'email', ''));
    $footerAddress = trim((string) data_get($contact, 'address', ''));
    $footerHours = trim((string) data_get($contact, 'hours', ''));
    $footerEitaa = trim((string) data_get($settings, 'eitaa_url', ''));
    $footerBale = trim((string) data_get($settings, 'bale_url', ''));
    $footerEnamad = trim((string) data_get($storefrontPageContent ?? [], 'page_home_enamad_url', ''));
    if (! \Illuminate\Support\Str::startsWith($footerEnamad, 'https://')) {
        $footerEnamad = '';
    }
@endphp
<footer class="farzin-site-footer farzin-site-footer--editorial fz-store-footer">
    <x-layout.container class="fz-store-footer__container">
        <div class="fz-store-footer__main">
            <div class="fz-store-footer__brand">
                <a href="{{ route('home') }}" class="fz-store-footer__logo farzin-footer-editorial__logo" aria-label="{{ $brandName }}، صفحه اصلی">
                    @if($brandLogoUrl)
                        <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" width="160" height="48" loading="lazy" decoding="async" class="fz-store-footer__logo-image">
                    @else
                        {{ $brandName }}
                    @endif
                </a>
                
                <p>فروشگاه تخصصی تجهیزات آشپزخانه؛ هود، اجاق گاز و سینک را با مشخصات، قیمت و اطلاعات هر مدل مقایسه کنید.</p>
                <x-layout.social-links :contact="$contact" :brand-name="$brandName" />
                @if($footerEitaa !== '' || $footerBale !== '')
                    <div class="fz-store-footer__extra-messengers" aria-label="پیام‌رسان‌های دیگر">
                        @if($footerEitaa !== '')<a href="{{ $footerEitaa }}" target="_blank" rel="noopener noreferrer">ایتا</a>@endif
                        @if($footerBale !== '')<a href="{{ $footerBale }}" target="_blank" rel="noopener noreferrer">بله</a>@endif
                    </div>
                @endif
                @if($footerEnamad !== '')
                    <a class="fz-store-footer__trust-link mt-3 inline-flex items-center gap-1 text-xs font-bold text-[var(--livora-accent)]" href="{{ $footerEnamad }}" target="_blank" rel="noopener noreferrer">نماد اعتماد الکترونیکی <span aria-hidden="true">↗</span></a>
                @endif
            </div>
            <nav class="fz-store-footer__column farzin-footer-editorial__nav" aria-label="پیوندهای اصلی">
                <h2>فروشگاه</h2><a href="{{ route('home') }}">خانه</a><a href="{{ route('shop.index') }}">همه محصولات</a><a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a><a href="{{ route('blog.index') }}">راهنمای خرید و مجله</a><a href="{{ route('about') }}">درباره ما</a><a href="{{ route('contact') }}">تماس با ما</a>
            </nav>
            <nav class="fz-store-footer__column" aria-label="خدمات مشتریان">
                <h2>خدمات مشتریان</h2><a href="{{ route('contact') }}">تماس و راهنمایی پیش از خرید</a>
                @auth
                    @if(auth()->user()->isCustomer())<a href="{{ route('account.orders.index') }}">سفارش‌های من</a><a href="{{ route('account.index') }}">حساب کاربری</a>@endif
                @else
                    <a href="{{ route('login', ['intended' => '/account/orders']) }}">ورود و پیگیری سفارش</a>
                @endauth
                <a href="{{ route('shipping-returns') }}">ارسال و مرجوعی</a><a href="{{ route('warranty') }}">ضمانت و خدمات پس از فروش</a><a href="{{ route('payment-installments') }}">خرید اقساطی</a><a href="{{ route('faq') }}">پرسش‌های متداول</a><a href="{{ route('terms') }}">قوانین و شرایط</a><a href="{{ route('privacy') }}">حریم خصوصی</a>
            </nav>
            <div class="fz-store-footer__contact farzin-footer-editorial__contact">
                <h2>ارتباط با {{ $brandName }}</h2>
                @if($footerPhone)<a href="{{ $footerPhone['href'] }}" dir="ltr">{{ $footerPhone['display'] }}</a>@endif
                @if($footerEmail !== '')<a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a>@endif
                @if($footerAddress !== '')<p>{{ $footerAddress }}</p>@endif
                @if($footerHours !== '')<p class="fz-store-footer__hours">ساعات پاسخ‌گویی: {{ $footerHours }}</p>@endif
                @if(! $footerPhone && $footerEmail === '' && $footerAddress === '' && ! $footerWhatsapp)<p>برای ارسال پرسش، از فرم تماس با {{ $brandName }} استفاده کنید.</p>@endif
                <a class="fz-store-footer__contact-link" href="{{ route('contact') }}">رفتن به صفحه تماس <span aria-hidden="true">←</span></a>
            </div>
        </div>
        <div class="fz-store-footer__bottom"><span>© {{ \App\Helpers\JalaliHelper::format(now(), 'Y') }} {{ $brandName }} · تمامی حقوق محفوظ است.</span><span>طراحی‌شده برای انتخاب بهتر تجهیزات آشپزخانه</span></div>
    </x-layout.container>
</footer>
