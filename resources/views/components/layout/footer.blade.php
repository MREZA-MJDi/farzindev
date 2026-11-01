<footer class="farzin-site-footer farzin-site-footer--editorial">
    <x-layout.container class="farzin-site-footer__container">
        @php
            $footerPhone = trim((string) data_get($storefrontContact, 'phone', ''));
            $footerPhoneHref = preg_replace('/[^0-9+]/', '', $footerPhone);
            $footerWhatsapp = preg_replace('/\D+/', '', (string) data_get($storefrontContact, 'whatsapp', ''));
        @endphp

        <div class="farzin-footer-editorial__brand">
            <a href="{{ route('home') }}" aria-label="فرزین، صفحه اصلی" class="farzin-footer-editorial__logo">فرزین</a>
            <p>تجهیزات آشپزخانه برای انتخابی هماهنگ با فضا و نیاز روزمره.</p>
        </div>

        <nav class="farzin-footer-editorial__nav" aria-label="پیوندهای اصلی">
            <a href="{{ route('home') }}">خانه</a>
            <a href="{{ route('shop.index') }}">فروشگاه</a>
            <a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a>
            <a href="{{ route('blog.index') }}">مجله و راهنمای خرید</a>
            <a href="{{ route('about') }}">درباره ما</a>
            <a href="{{ route('contact') }}">تماس با ما</a>
        </nav>

        <div class="farzin-footer-editorial__contact" aria-label="راه‌های ارتباطی">
            @if($footerPhone !== '' && $footerPhoneHref !== '')
                <a href="tel:{{ $footerPhoneHref }}" dir="ltr">{{ $footerPhone }}</a>
            @endif
            @if(filled(data_get($storefrontContact, 'email')))
                <a href="mailto:{{ data_get($storefrontContact, 'email') }}">{{ data_get($storefrontContact, 'email') }}</a>
            @endif
            @if($footerWhatsapp !== '')
                <a href="https://wa.me/{{ $footerWhatsapp }}" target="_blank" rel="noopener noreferrer">واتساپ</a>
            @endif
            @if(filled(data_get($storefrontContact, 'instagram_url')))
                <a href="{{ data_get($storefrontContact, 'instagram_url') }}" target="_blank" rel="noopener noreferrer">اینستاگرام</a>
            @endif
            @if(filled(data_get($storefrontContact, 'telegram_url')))
                <a href="{{ data_get($storefrontContact, 'telegram_url') }}" target="_blank" rel="noopener noreferrer">تلگرام</a>
            @endif
        </div>

        <div class="farzin-footer-editorial__bottom">
            <span>© {{ \App\Helpers\JalaliHelper::format(now(), 'Y') }} فرزین · تمامی حقوق محفوظ است.</span>
            <a href="{{ route('contact') }}">برای راهنمایی با ما در تماس باشید</a>
        </div>
    </x-layout.container>
</footer>
