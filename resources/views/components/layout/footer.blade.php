@props(['contact' => []])

@php
    $safeWebUrl = static function ($value): ?string {
        $url = trim((string) $value);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    };

    $instagramUrl = $safeWebUrl($contact['instagram_url'] ?? null);
    $telegramUrl = $safeWebUrl($contact['telegram_url'] ?? null);

    $whatsappValue = trim((string) ($contact['whatsapp'] ?? ''));
    $whatsappUrl = null;

    if ($whatsappValue !== '' && filter_var($whatsappValue, FILTER_VALIDATE_URL)) {
        $whatsappHost = strtolower((string) parse_url($whatsappValue, PHP_URL_HOST));

        if (in_array($whatsappHost, ['wa.me', 'www.wa.me', 'api.whatsapp.com'], true)) {
            $whatsappUrl = $whatsappValue;
        }
    }

    if ($whatsappUrl === null) {
        $whatsappNumber = preg_replace('/\D+/', '', $whatsappValue !== '' ? $whatsappValue : (string) ($contact['phone'] ?? ''));

        if (str_starts_with($whatsappNumber, '00')) {
            $whatsappNumber = substr($whatsappNumber, 2);
        } elseif (str_starts_with($whatsappNumber, '0')) {
            $whatsappNumber = '98' . substr($whatsappNumber, 1);
        }

        if (strlen($whatsappNumber) >= 8) {
            $whatsappUrl = 'https://wa.me/' . $whatsappNumber;
        }
    }
@endphp

<footer class="fz-global-footer" aria-label="پاورقی وب‌سایت فرزین">
    <x-layout.container class="fz-global-footer__inner">
        <div class="fz-global-footer__brand">
            <a href="{{ route('home') }}" class="fz-global-footer__wordmark" aria-label="فرزین، صفحه اصلی">
                <span>فرزین</span>
                <small>FARZIN / KITCHEN EQUIPMENT</small>
            </a>
            <p>تجهیزات آشپزخانه فرزین؛ انتخاب آگاهانه برای خانه‌ای هماهنگ‌تر.</p>
        </div>

        <nav class="fz-global-footer__nav" aria-label="پیوندهای پایین صفحه">
            <a href="{{ route('home') }}">خانه</a>
            <a href="{{ route('shop.index') }}">فروشگاه</a>
            <a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a>
            <a href="{{ route('blog.index') }}">راهنمای خرید</a>
            <a href="{{ route('about') }}">درباره ما</a>
            <a href="{{ route('contact') }}">تماس با ما</a>
        </nav>

        <div class="fz-global-footer__bottom">
            <p>© {{ \App\Helpers\JalaliHelper::format(now(), 'Y') }} فرزین. تمامی حقوق محفوظ است.</p>

            <div class="fz-global-footer__socials" aria-label="شبکه‌های اجتماعی">
                @if($instagramUrl)
                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="17.6" cy="6.6" r="1.1" fill="currentColor"/>
                        </svg>
                    </a>
                @endif

                @if($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20.2 11.7a8.2 8.2 0 0 1-12.1 7.2L3.2 20l1.1-4.7a8.2 8.2 0 1 1 15.9-3.6Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8.4 7.8c.2-.4.4-.4.7-.4h.5c.2 0 .4.1.5.4l.8 1.8c.1.2.1.4-.1.6l-.6.7c-.2.2-.2.4-.1.6.5.9 1.3 1.7 2.2 2.2.2.1.4.1.6-.1l.8-.9c.2-.2.4-.2.6-.1l1.7.8c.3.1.4.3.4.5 0 .5-.2 1-.6 1.3-.5.4-1.1.6-1.8.5-1.1-.2-2.4-.9-3.6-2-1.2-1.1-2-2.5-2.2-3.5-.2-.8 0-1.6.3-2.1Z" fill="currentColor"/>
                        </svg>
                    </a>
                @endif

                @if($telegramUrl)
                    <a href="{{ $telegramUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m21 3-3.2 18-6.1-5.1-3.2 3 .5-5.7L19 5.2 7.8 11.9l-4.8-1.6L21 3Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </x-layout.container>
</footer>
