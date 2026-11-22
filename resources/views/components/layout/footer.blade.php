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
    $footerEnamadParts = parse_url($footerEnamad);
    $footerEnamadQuery = [];

    if (is_array($footerEnamadParts)) {
        parse_str((string) ($footerEnamadParts['query'] ?? ''), $footerEnamadQuery);
    }

    $footerEnamadId = trim((string) ($footerEnamadQuery['id'] ?? ''));
    $footerEnamadCode = trim((string) ($footerEnamadQuery['Code'] ?? ''));
    $footerEnamadIsOfficial = is_array($footerEnamadParts)
        && strtolower((string) ($footerEnamadParts['scheme'] ?? '')) === 'https'
        && strtolower((string) ($footerEnamadParts['host'] ?? '')) === 'trustseal.enamad.ir'
        && in_array((string) ($footerEnamadParts['path'] ?? ''), ['', '/'], true)
        && ! isset($footerEnamadParts['port'], $footerEnamadParts['user'])
        && preg_match('/^\d{1,12}$/', $footerEnamadId) === 1
        && preg_match('/^[A-Za-z0-9]{8,128}$/', $footerEnamadCode) === 1;

    if (! \Illuminate\Support\Str::startsWith($footerEnamad, 'https://')) {
        $footerEnamad = '';
    }

    $footerEnamadLogoUrl = $footerEnamadIsOfficial
        ? 'https://trustseal.enamad.ir/logo.aspx?id=' . rawurlencode($footerEnamadId) . '&Code=' . rawurlencode($footerEnamadCode)
        : '';

    $footerEnamadImagePath = \Illuminate\Support\Facades\Cache::remember(
        'storefront.enamad-badge-image.v1',
        now()->addMinutes(10),
        fn () => \App\Models\StorefrontSetting::query()->where('key', 'page_home_enamad_image_path')->value('value')
    );

    $footerEnamadUploadedImageUrl = null;
    if ($footerEnamadImagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($footerEnamadImagePath)) {
        $footerEnamadUploadedImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($footerEnamadImagePath);
    }

    $footerHasTrustBadge = $footerEnamad !== '' || $footerEnamadUploadedImageUrl !== null;
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
        @if($footerHasTrustBadge)
            <section class="fz-store-footer__trust-row" aria-label="نماد اعتماد الکترونیکی">
                <div class="fz-store-footer__trust-copy">
                    <span>خرید امن و آگاهانه</span>
                    <h2>نماد اعتماد الکترونیکی</h2>
                    <p>برای مشاهده اطلاعات و اعتبار نماد، روی نشان کلیک کنید.</p>
                </div>
                @if($footerEnamad !== '')
                    <a
                        class="fz-store-footer__trust-link"
                        href="{{ $footerEnamad }}"
                        target="_blank"
                        rel="noopener"
                        referrerpolicy="origin"
                        aria-label="مشاهده و استعلام نماد اعتماد الکترونیکی {{ $brandName }}"
                    >
                        @if($footerEnamadLogoUrl !== '')
                            <img
                                src="{{ $footerEnamadLogoUrl }}"
                                alt="نماد اعتماد الکترونیکی {{ $brandName }}"
                                width="96"
                                height="96"
                                loading="lazy"
                                decoding="async"
                                referrerpolicy="origin"
                                code="{{ $footerEnamadCode }}"
                            >
                        @elseif($footerEnamadUploadedImageUrl)
                            <img
                                src="{{ $footerEnamadUploadedImageUrl }}"
                                alt="نماد اعتماد الکترونیکی {{ $brandName }}"
                                width="96"
                                height="96"
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <span class="fz-store-footer__trust-placeholder">نماد اعتماد الکترونیکی</span>
                        @endif
                    </a>
                @elseif($footerEnamadUploadedImageUrl)
                    <div class="fz-store-footer__trust-link" aria-label="نماد اعتماد الکترونیکی">
                        <img
                            src="{{ $footerEnamadUploadedImageUrl }}"
                            alt="نماد اعتماد الکترونیکی {{ $brandName }}"
                            width="96"
                            height="96"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                @endif
            </section>
        @endif
        <div class="fz-store-footer__bottom"><span>© {{ \App\Helpers\JalaliHelper::format(now(), 'Y') }} {{ $brandName }} · تمامی حقوق محفوظ است.</span><span>طراحی‌شده برای انتخاب بهتر تجهیزات آشپزخانه</span></div>
    </x-layout.container>
</footer>
