@props(['contact' => []])

@php
    $whatsappDigits = preg_replace('/\D+/', '', (string) data_get($contact, 'whatsapp', ''));

    if ($whatsappDigits !== '') {
        if (str_starts_with($whatsappDigits, '0')) {
            $whatsappDigits = '98' . substr($whatsappDigits, 1);
        } elseif (! str_starts_with($whatsappDigits, '98')) {
            $whatsappDigits = '98' . $whatsappDigits;
        }
    }

    $phone = preg_replace('/[^0-9+]/', '', (string) data_get($contact, 'phone', ''));
    $eitaaUrl = trim((string) data_get($contact, 'eitaa_url', ''));
    $telegramUrl = trim((string) data_get($contact, 'telegram_url', ''));

    $contactUrl = $whatsappDigits !== ''
        ? 'https://wa.me/' . $whatsappDigits
        : ($phone !== '' ? 'tel:' . $phone : ($eitaaUrl ?: $telegramUrl));

    $contactLabel = $whatsappDigits !== ''
        ? 'مشاوره در واتساپ'
        : ($phone !== '' ? 'تماس با فرزین' : 'ارتباط با فرزین');
    $externalContact = $whatsappDigits !== '' || ($phone === '' && ($eitaaUrl !== '' || $telegramUrl !== ''));
@endphp

@if($contactUrl !== '')
    <a
        href="{{ $contactUrl }}"
        class="fixed bottom-24 right-4 z-[95] inline-flex min-h-12 items-center gap-2 rounded-full border border-white/30 bg-[#075E54] px-4 py-3 text-xs font-bold text-white shadow-xl transition hover:-translate-y-0.5 hover:bg-[#064D45] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#075E54] lg:bottom-6 lg:right-6"
        @if($externalContact) target="_blank" rel="noopener noreferrer" @endif
        aria-label="{{ $contactLabel }}"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 shrink-0" aria-hidden="true">
            <path d="M20.5 11.5a8.5 8.5 0 0 1-12.7 7.3L3 20l1.2-4.5A8.5 8.5 0 1 1 20.5 11.5Z"/>
            <path d="M8.5 8.5c.5 2.7 2.3 4.5 5 5"/>
        </svg>
        <span>{{ $contactLabel }}</span>
    </a>
@endif
