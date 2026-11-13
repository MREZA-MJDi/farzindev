@props(['contact' => [], 'brandName' => 'فرجی‌هوم', 'showPhone' => true])

@php
    $phone = $showPhone ? \\App\\Support\\StorefrontContact::phone(data_get($contact, 'phone')) : null;
    $whatsapp = \\App\\Support\\StorefrontContact::whatsapp(data_get($contact, 'whatsapp'));
    $instagram = trim((string) data_get($contact, 'instagram_url', ''));
    $telegram = trim((string) data_get($contact, 'telegram_url', ''));
    $safeExternal = static function (string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL)
            && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true);
    };
@endphp

<div class="fz-social-links" aria-label="راه‌های ارتباط آنلاین">
    @if($instagram !== '' && $safeExternal($instagram))
        <a class="fz-social-links__item fz-social-links__item--instagram" href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" aria-label="اینستاگرام {{ $brandName }}" title="اینستاگرام">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.8" r="1.2" fill="currentColor"/></svg>
        </a>
    @endif
    @if($telegram !== '' && $safeExternal($telegram))
        <a class="fz-social-links__item fz-social-links__item--telegram" href="{{ $telegram }}" target="_blank" rel="noopener noreferrer" aria-label="تلگرام {{ $brandName }}" title="تلگرام">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.7 4.4 18.5 20c-.2 1.1-.9 1.4-1.8.9l-5-3.7-2.4 2.3c-.3.3-.6.6-1.2.6l.4-5.1 9.3-8.4c.4-.4-.1-.6-.6-.2L5.7 13.6.8 12c-1.1-.3-1.1-1 .2-1.5L20.2 3c.9-.3 1.7.2 1.5 1.4Z"/></svg>
        </a>
    @endif
    @if($whatsapp)
        <a class="fz-social-links__item fz-social-links__item--whatsapp" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" aria-label="واتساپ {{ $brandName }}" title="واتساپ">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12.1 2A9.8 9.8 0 0 0 3.7 17l-1.3 4.8 4.9-1.3A9.9 9.9 0 1 0 12.1 2Zm0 17.8a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12.1 19.8Zm4.4-6c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.8.9-.3.2-.5.1a6.5 6.5 0 0 1-1.9-1.2 7.1 7.1 0 0 1-1.3-1.6c-.1-.2 0-.3.1-.5l.4-.4.2-.4c.1-.2 0-.3 0-.4s-.5-1.3-.7-1.8c-.2-.5-.4-.4-.5-.4h-.5c-.2 0-.4.1-.6.3s-.8.8-.8 1.9.8 2.2.9 2.4a9.3 9.3 0 0 0 3.6 3.2c.5.2.9.4 1.2.5.5.2 1 .2 1.4.1.4-.1 1.4-.6 1.6-1.1s.2-1 .2-1.1-.2-.2-.4-.3Z"/></svg>
        </a>
    @endif
    @if($phone)
        <a class="fz-social-links__item fz-social-links__item--phone" href="{{ $phone['href'] }}" aria-label="تماس تلفنی با {{ $brandName }}" title="تماس تلفنی">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.2 11 11 0 0 0 3.4.5 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17.8 17.8 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11 11 0 0 0 .5 3.4 1 1 0 0 1-.2 1Z"/></svg>
        </a>
    @endif
</div>
