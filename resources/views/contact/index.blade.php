@extends('layouts.app')

@section('title', 'تماس با فرزین | ارتباط با ما')

@section(
    'description',
    'برای مشاوره انتخاب هود و سینک آشپزخانه، بررسی مشخصات و پیگیری سفارش با فرزین در ارتباط باشید.'
)

@section('canonical', route('contact'))

@push('seo')

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="تماس با فرزین | ارتباط با ما"
    >

    <meta
        property="og:description"
        content="برای مشاوره خرید، پیگیری سفارش و دریافت اطلاعات بیشتر با فرزین در ارتباط باشید."
    >

    <meta
        property="og:url"
        content="{{ route('contact') }}"
    >

    <meta
        name="twitter:card"
        content="summary"
    >

    <meta
        name="twitter:title"
        content="تماس با فرزین"
    >

    <meta
        name="twitter:description"
        content="راه‌های ارتباطی و راهنمای خرید فرزین."
    >

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "فرزین",
        "url": @json(url('/')),
        "potentialAction": {
            "@@type": "SearchAction",
            "target": @json(url('/shop') . '?search={search_term_string}'),
            "query-input": "required name=search_term_string"
        }
    }
    </script>

@endpush


@section('content')

    <div class="overflow-hidden bg-[var(--livora-cream)]">

        {{-- Compact editorial hero: clear copy on the right, motion-led brand visual on the left. --}}
        <section class="farzin-contact-hero relative overflow-hidden" data-farzin-contact-hero aria-labelledby="contact-hero-title">
            <div class="farzin-contact-hero__grid" aria-hidden="true"></div>
            <div class="farzin-contact-hero__glow" aria-hidden="true"></div>

            <x-layout.container>
                <div class="farzin-contact-hero__breadcrumb">
                    <a href="{{ route('home') }}">خانه</a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">تماس با ما</span>
                </div>

                <div class="farzin-contact-hero__layout">
                    <div class="farzin-contact-hero__copy">
                        <p class="farzin-contact-hero__eyebrow" data-contact-reveal>
                            <span aria-hidden="true"></span>
                            FARZIN / CONTACT
                        </p>

                        <h1 id="contact-hero-title" class="farzin-contact-hero__title" data-contact-reveal>
                            برای انتخاب بهتر،
                            <span>کنار شما هستیم.</span>
                        </h1>

                        <p class="farzin-contact-hero__description" data-contact-reveal>
                            برای انتخاب هود و سینک، بررسی مشخصات یا پیگیری سفارش، مستقیم با تیم فرزین در ارتباط باشید.
                        </p>

                        <div class="farzin-contact-hero__actions" data-contact-reveal>
                            <a href="#contact-form" class="farzin-contact-hero__primary">
                                ارسال پیام
                                <span aria-hidden="true">↙</span>
                            </a>
                            @if($contactSetting?->phone)
                                <a href="tel:{{ $contactSetting->phone }}" class="farzin-contact-hero__secondary">
                                    تماس مستقیم
                                </a>
                            @else
                                <a href="{{ route('shop.index') }}" class="farzin-contact-hero__secondary">
                                    مشاهده محصولات
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="farzin-contact-hero__visual" data-contact-art role="img" aria-label="تصویرسازی متحرک از محصولات و ارتباط با فرزین">
                        <div class="farzin-contact-hero__ring farzin-contact-hero__ring--outer" aria-hidden="true"></div>
                        <div class="farzin-contact-hero__ring farzin-contact-hero__ring--inner" aria-hidden="true"></div>
                        <div class="farzin-contact-hero__visual-index"><span>FZ</span><i aria-hidden="true"></i><span>SUPPORT / 24</span></div>

                        <svg class="farzin-contact-hero__product" viewBox="0 0 620 420" fill="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="contactSteel" x1="160" y1="110" x2="425" y2="280" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#F7FAFA"/>
                                    <stop offset=".32" stop-color="#AEB9BC"/>
                                    <stop offset=".54" stop-color="#E9EEEF"/>
                                    <stop offset=".78" stop-color="#7D898D"/>
                                    <stop offset="1" stop-color="#DDE4E6"/>
                                </linearGradient>
                                <linearGradient id="contactGlass" x1="190" y1="210" x2="460" y2="300" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#343D41"/>
                                    <stop offset=".5" stop-color="#14191B"/>
                                    <stop offset="1" stop-color="#5B666A"/>
                                </linearGradient>
                                <filter id="contactShadow" x="40" y="40" width="540" height="360" filterUnits="userSpaceOnUse">
                                    <feGaussianBlur stdDeviation="18"/>
                                </filter>
                            </defs>
                            <ellipse cx="314" cy="335" rx="175" ry="24" fill="#000" fill-opacity=".35" filter="url(#contactShadow)"/>
                            <g class="farzin-contact-hero__hood">
                                <path d="M244 58H367V164H244V58Z" fill="url(#contactSteel)" stroke="#F7FAFA" stroke-opacity=".5"/>
                                <path d="M194 163H420L475 224H141L194 163Z" fill="url(#contactSteel)" stroke="#F7FAFA" stroke-opacity=".7"/>
                                <path d="M156 225H459L438 256H178L156 225Z" fill="url(#contactGlass)" stroke="#AEB9BC" stroke-opacity=".65"/>
                                <path d="M208 238H408" stroke="#F7FAFA" stroke-opacity=".35" stroke-width="2"/>
                                <path d="M236 248H380" stroke="#A4BDC0" stroke-opacity=".75" stroke-width="3" stroke-linecap="round"/>
                                <path d="M268 77H344" stroke="#fff" stroke-opacity=".6" stroke-width="2"/>
                                <path d="M267 91H344" stroke="#7D898D" stroke-opacity=".4" stroke-width="1.5"/>
                                <path d="M215 177H399" stroke="#fff" stroke-opacity=".35" stroke-width="2"/>
                            </g>
                            <g class="farzin-contact-hero__message">
                                <rect x="337" y="258" width="176" height="92" rx="18" fill="#F8FAFA" fill-opacity=".97" stroke="#D7E0E2"/>
                                <circle cx="362" cy="282" r="5" fill="#355F65"/>
                                <path d="M376 282H478" stroke="#A5B2B6" stroke-width="4" stroke-linecap="round"/>
                                <path d="M357 300H470" stroke="#D0D8DA" stroke-width="4" stroke-linecap="round"/>
                                <path d="M357 314H442" stroke="#D0D8DA" stroke-width="4" stroke-linecap="round"/>
                                <path d="M393 350L383 363L415 350" fill="#F8FAFA" stroke="#D7E0E2" stroke-linejoin="round"/>
                            </g>
                            <g class="farzin-contact-hero__signal">
                                <circle cx="144" cy="116" r="6" fill="#A4BDC0"/>
                                <circle cx="144" cy="116" r="18" stroke="#A4BDC0" stroke-opacity=".5"/>
                                <circle cx="144" cy="116" r="31" stroke="#A4BDC0" stroke-opacity=".22"/>
                            </g>
                        </svg>

                        <div class="farzin-contact-hero__note">
                            <span class="farzin-contact-hero__note-line" aria-hidden="true"></span>
                            <span>HOODS · SINKS · SUPPORT</span>
                        </div>
                    </div>
                </div>
            </x-layout.container>
        </section>


        {{-- =========================================================
             CONTACT METHODS
        ========================================================== --}}

        <section class="border-b border-[var(--livora-border)]">

            <x-layout.container>

                <div class="grid gap-3 py-6 sm:grid-cols-2 lg:grid-cols-4">

                    {{-- PHONE --}}

                    @if($contactSetting?->phone)

                        <a
                            href="tel:{{ $contactSetting->phone }}"
                            class="group rounded-[1.5rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-ink)]"
                        >

                            @else

                                <div
                                    class="rounded-[1.5rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5"
                                >

                                    @endif

                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xs font-semibold">
                                        01
                                    </div>

                                    <p class="mt-4 text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                        PHONE
                                    </p>

                                    <h2 class="mt-2 text-base font-semibold">
                                        {{ $contactSetting?->phone_label ?? 'تماس تلفنی' }}
                                    </h2>

                                    <p class="mt-3 break-all text-xs leading-6 text-[var(--livora-stone)]">
                                        {{ $contactSetting?->phone ?? 'شماره تماس ثبت نشده است.' }}
                                    </p>

                                @if($contactSetting?->phone)

                        </a>

                    @else

                </div>

                @endif


                {{-- EMAIL --}}

                @if($contactSetting?->email)

                    <a
                        href="mailto:{{ $contactSetting->email }}"
                        class="group rounded-[1.5rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-ink)]"
                    >

                        @else

                            <div
                                class="rounded-[1.5rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5"
                            >

                                @endif

                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xs font-semibold">
                                    02
                                </div>

                                <p class="mt-4 text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    EMAIL
                                </p>

                                <h2 class="mt-2 text-base font-semibold">
                                    {{ $contactSetting?->email_label ?? 'ایمیل' }}
                                </h2>

                                <p class="mt-3 break-all text-xs leading-6 text-[var(--livora-stone)]">
                                    {{ $contactSetting?->email ?? 'ایمیل ثبت نشده است.' }}
                                </p>

                            @if($contactSetting?->email)

                    </a>

        @else

    </div>

    @endif


    {{-- SUPPORT --}}

    <div class="rounded-[1.5rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xs font-semibold">
            03
        </div>

        <p class="mt-4 text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
            SUPPORT
        </p>

        <h2 class="mt-2 text-base font-semibold">
            {{ $contactSetting?->support_label ?? 'پشتیبانی' }}
        </h2>

        <p class="mt-3 text-xs leading-6 text-[var(--livora-stone)]">
            {{ $contactSetting?->support_description ?? 'پاسخ‌گویی به پرسش‌های خرید و سفارش.' }}
        </p>

    </div>


    {{-- ONLINE --}}

    <div class="rounded-[1.5rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xs font-semibold">
            04
        </div>

        <p class="mt-4 text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
            ONLINE
        </p>

        <h2 class="mt-2 text-base font-semibold">
            {{ $contactSetting?->online_label ?? 'ارتباط آنلاین' }}
        </h2>

        <p class="mt-3 text-xs leading-6 text-[var(--livora-stone)]">
            {{ $contactSetting?->online_description ?? 'پیام خود را ارسال کنید تا با شما تماس بگیریم.' }}
        </p>

    </div>

    </div>

    </x-layout.container>

    </section>


    {{-- =========================================================
         FORM + INFO
    ========================================================== --}}

    <section id="contact-form">

        <x-layout.container>

            <div class="grid gap-6 py-8 sm:py-10 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:py-12">

                {{-- INFO --}}

                <div>

                    <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                        LET'S TALK
                    </p>

                    <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                        چه کمکی از دست ما برمی‌آید؟
                    </h2>

                    <p class="mt-5 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">
                        اگر درباره محصول، موجودی، قیمت، شرایط خرید اقساطی،
                        ارسال یا سفارش خود سؤالی دارید، پیام بگذارید.
                    </p>


                    {{-- ADDRESS --}}

                    <div class="mt-6 space-y-3">

                        <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                ADDRESS
                            </p>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $contactSetting?->address ?? 'آدرس فروشگاه ثبت نشده است.' }}
                            </p>

                        </div>


                        {{-- HOURS --}}

                        <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                HOURS
                            </p>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $contactSetting?->hours ?? 'ساعات کاری فروشگاه ثبت نشده است.' }}
                            </p>

                        </div>


                        @php
                            $mapAddress = trim((string) ($contactSetting?->address ?? ''));
                            $whatsappDigits = preg_replace('/\D+/', '', (string) ($contactSetting?->whatsapp ?? ''));
                            if ($whatsappDigits !== '') {
                                if (str_starts_with($whatsappDigits, '0')) {
                                    $whatsappDigits = '98' . substr($whatsappDigits, 1);
                                } elseif (! str_starts_with($whatsappDigits, '98')) {
                                    $whatsappDigits = '98' . $whatsappDigits;
                                }
                            }
                        @endphp

                        {{-- GOOGLE MAP --}}

                        @if($mapAddress !== '')
                            <div class="overflow-hidden rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)]">
                                <div class="flex items-center justify-between gap-3 p-4">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">LOCATION</p>
                                        <h3 class="mt-2 text-sm font-semibold">موقعیت روی نقشه</h3>
                                        <p class="mt-1 text-xs leading-6 text-[var(--livora-stone)]">{{ $mapAddress }}</p>
                                    </div>
                                    <a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($mapAddress) }}" target="_blank" rel="noopener noreferrer" class="shrink-0 text-xs font-medium text-[var(--livora-accent)]">مسیریابی ↗</a>
                                </div>
                                <iframe
                                    title="نقشه آدرس فرزین"
                                    src="https://www.google.com/maps?q={{ urlencode($mapAddress) }}&amp;output=embed"
                                    width="100%"
                                    height="340"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    class="block w-full border-0" style="min-height:300px"
                                ></iframe>
                            </div>
                        @endif

                        {{-- SOCIAL / VIRTUAL CONTACT --}}

                        @if($whatsappDigits !== '' || filled($contactSetting?->instagram_url))
                            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">
                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">ONLINE CONTACT</p>
                                <h3 class="mt-2 text-sm font-semibold">ارتباط در فضای مجازی</h3>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if($whatsappDigits !== '')
                                        <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-xl border border-[var(--livora-border)] px-4 py-2.5 text-xs font-medium transition hover:border-[var(--livora-ink)]">واتساپ ↗</a>
                                    @endif
                                    @if(filled($contactSetting?->instagram_url))
                                        <a href="{{ $contactSetting->instagram_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-xl border border-[var(--livora-border)] px-4 py-2.5 text-xs font-medium transition hover:border-[var(--livora-ink)]">اینستاگرام ↗</a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- INSTALLMENT --}}

                        <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                INSTALLMENT
                            </p>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                برای اطلاع از شرایط اقساط هر محصول،
                                صفحه همان محصول را بررسی کنید یا از طریق فرم با ما تماس بگیرید.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- FORM --}}

                <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8 lg:p-10">

                    <div class="mb-8">

                        <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            CONTACT FORM
                        </p>

                        <h2 class="mt-3 text-2xl font-semibold">
                            پیام خود را ارسال کنید
                        </h2>

                        <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                            اطلاعات تماس خود را وارد کنید تا بتوانیم پاسخ دقیق‌تری ارائه دهیم.
                        </p>

                    </div>


                    {{-- SUCCESS MESSAGE --}}

                    @if(session('success'))

                        <div class="mb-6 rounded-2xl border border-[var(--livora-success)]/20 bg-[var(--livora-success-soft)] p-4">

                            <p class="text-xs leading-7 text-[var(--livora-success)]">
                                {{ session('success') }}
                            </p>

                        </div>

                    @endif


                    {{-- VALIDATION ERRORS --}}

                    @if($errors->any())

                        <div class="mb-6 rounded-2xl border border-[var(--livora-danger)]/20 bg-[var(--livora-danger-soft)] p-4">

                            <ul class="space-y-1 text-xs leading-6 text-[var(--livora-danger)]">

                                @foreach($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    {{-- CONTACT FORM --}}

                    <form
                        action="{{ route('contact.store') }}"
                        method="POST"
                        class="space-y-5"
                    >

                        @csrf


                        {{-- NAME + PHONE --}}

                        <div class="grid gap-5 sm:grid-cols-2">

                            {{-- NAME --}}

                            <div>

                                <label
                                    for="name"
                                    class="admin-label"
                                >
                                    نام و نام خانوادگی
                                </label>

                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name', auth()->user()?->name) }}"
                                    class="admin-input mt-2 w-full @error('name') border-[var(--livora-danger)] @enderror"
                                    autocomplete="name"
                                    placeholder="نام شما"
                                >

                                @error('name')

                                <p class="mt-1 text-[11px] text-[var(--livora-danger)]">
                                    {{ $message }}
                                </p>

                                @enderror

                            </div>


                            {{-- PHONE --}}

                            <div>

                                <label
                                    for="phone"
                                    class="admin-label"
                                >
                                    شماره تماس
                                </label>

                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ old('phone', auth()->user()?->phone) }}"
                                    class="admin-input mt-2 w-full @error('phone') border-[var(--livora-danger)] @enderror"
                                    autocomplete="tel"
                                    dir="ltr"
                                    placeholder="09xxxxxxxxx"
                                >

                                @error('phone')

                                <p class="mt-1 text-[11px] text-[var(--livora-danger)]">
                                    {{ $message }}
                                </p>

                                @enderror

                            </div>

                        </div>


                        {{-- EMAIL --}}

                        <div>

                            <label
                                for="email"
                                class="admin-label"
                            >
                                ایمیل
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', auth()->user()?->email) }}"
                                class="admin-input mt-2 w-full @error('email') border-[var(--livora-danger)] @enderror"
                                autocomplete="email"
                                dir="ltr"
                                placeholder="you@example.com"
                            >

                            @error('email')

                            <p class="mt-1 text-[11px] text-[var(--livora-danger)]">
                                {{ $message }}
                            </p>

                            @enderror

                        </div>


                        {{-- SUBJECT --}}

                        <div>

                            <label
                                for="subject"
                                class="admin-label"
                            >
                                موضوع
                            </label>

                            <select
                                id="subject"
                                name="subject"
                                class="admin-select mt-2 w-full @error('subject') border-[var(--livora-danger)] @enderror"
                            >

                                <option value="">
                                    انتخاب موضوع
                                </option>

                                <option
                                    value="product"
                                    @selected(old('subject') === 'product')
                                >
                                مشاوره درباره محصول
                                </option>

                                <option
                                    value="installment"
                                    @selected(old('subject') === 'installment')
                                >
                                شرایط خرید اقساطی
                                </option>

                                <option
                                    value="order"
                                    @selected(old('subject') === 'order')
                                >
                                پیگیری سفارش
                                </option>

                                <option
                                    value="shipping"
                                    @selected(old('subject') === 'shipping')
                                >
                                ارسال و تحویل
                                </option>

                                <option
                                    value="other"
                                    @selected(old('subject') === 'other')
                                >
                                سایر
                                </option>

                            </select>

                            @error('subject')

                            <p class="mt-1 text-[11px] text-[var(--livora-danger)]">
                                {{ $message }}
                            </p>

                            @enderror

                        </div>


                        {{-- MESSAGE --}}

                        <div>

                            <label
                                for="message"
                                class="admin-label"
                            >
                                پیام
                            </label>

                            <textarea
                                id="message"
                                name="message"
                                rows="7"
                                class="admin-textarea mt-2 w-full @error('message') border-[var(--livora-danger)] @enderror"
                                placeholder="پیام خود را بنویسید..."
                            >{{ old('message') }}</textarea>

                            @error('message')

                            <p class="mt-1 text-[11px] text-[var(--livora-danger)]">
                                {{ $message }}
                            </p>

                            @enderror

                        </div>


                        {{-- FORM NOTE --}}

                        <div class="rounded-2xl bg-[var(--livora-surface)] p-4">

                            <p class="text-[11px] leading-7 text-[var(--livora-stone)]">
                                پیام شما پس از ارسال بررسی می‌شود و در صورت نیاز
                                از طریق اطلاعات تماس واردشده با شما ارتباط خواهیم گرفت.
                            </p>

                        </div>


                        {{-- SUBMIT --}}

                        <button
                            type="submit"
                            class="w-full rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:bg-[var(--livora-accent)]"
                        >
                            ارسال پیام
                        </button>

                    </form>

                </div>

            </div>

        </x-layout.container>

    </section>


    {{-- =========================================================
         FAQ
         FAQ is intentionally static for the first version.
    ========================================================== --}}

    <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

        <x-layout.container>

            <div
                x-data="{ active: null }"
                class="grid gap-10 py-14 sm:py-16 lg:grid-cols-[0.7fr_1.3fr] lg:py-24"
            >

                <div>

                    <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                        FAQ
                    </p>

                    <h2 class="mt-4 text-3xl font-semibold tracking-tight">
                        پرسش‌های متداول
                    </h2>

                    <p class="mt-4 text-sm leading-8 text-[var(--livora-stone)]">
                        پاسخ چند سؤال رایج درباره ارتباط با فرزین و خرید از فروشگاه.
                    </p>

                </div>


                <div class="space-y-3">

                    @forelse($faqs as $index => $faq)

                        <article class="overflow-hidden rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-cream)]">

                            <button
                                type="button"
                                @click="active === {{ $index }} ? active = null : active = {{ $index }}"
                                :aria-expanded="active === {{ $index }} ? 'true' : 'false'"
                                class="flex w-full items-center justify-between gap-5 px-5 py-5 text-right sm:px-6"
                            >

                                    <span class="text-sm font-semibold">
                                        {{ $faq['question'] }}
                                    </span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-4 w-4 shrink-0 transition-transform duration-300"
                                    :class="active === {{ $index }} ? 'rotate-180' : ''"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m19.5 8.25-7.5 7.5-7.5-7.5"
                                    />
                                </svg>

                            </button>


                            <div
                                x-show="active === {{ $index }}"
                                x-collapse
                                x-cloak
                            >

                                <div class="border-t border-[var(--livora-border)] px-5 py-5 text-xs leading-7 text-[var(--livora-stone)] sm:px-6">
                                    {{ $faq['answer'] }}
                                </div>

                            </div>

                        </article>

                    @empty

                        <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-cream)] p-6">

                            <p class="text-sm leading-7 text-[var(--livora-stone)]">
                                در حال حاضر پرسش متداولی ثبت نشده است.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>

        </x-layout.container>

    </section>


    {{-- =========================================================
         FINAL CTA
    ========================================================== --}}

    <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

        <x-layout.container>

            <div class="flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between sm:py-16">

                <div>

                    <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                        فرزین
                    </p>

                    <h2 class="mt-2 text-2xl font-semibold">
                        آماده‌ای انتخابت را پیدا کنی؟
                    </h2>

                    <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">
                        مجموعه محصولات را ببین و انتخاب بعدی‌ات را پیدا کن.
                    </p>

                </div>


                <a
                    href="{{ route('shop.index') }}"
                    class="inline-flex w-fit rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                >
                    ورود به فروشگاه
                </a>

            </div>

        </x-layout.container>

    </section>

    </div>

@endsection
