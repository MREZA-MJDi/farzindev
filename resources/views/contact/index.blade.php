@extends('layouts.app')

@section('title', 'تماس با فرزین | ارتباط با ما')

@section(
    'description',
    'برای مشاوره انتخاب هود و سینک آشپزخانه، بررسی مشخصات و پیگیری سفارش با فرزین در ارتباط باشید.'
)

@section('canonical', route('contact'))




@section('body_class', 'contact-body min-h-screen antialiased')

@section('content')
    @php
        $mapAddress = trim((string) ($contactSetting?->address ?? ''));
        $mapLatitude = $contactSetting?->latitude;
        $mapLongitude = $contactSetting?->longitude;
        $hasMapCoordinates = is_numeric($mapLatitude) && is_numeric($mapLongitude);
        $mapQuery = $hasMapCoordinates
            ? (string) $mapLatitude . ',' . (string) $mapLongitude
            : $mapAddress;
        $whatsappDigits = preg_replace('/\D+/', '', (string) ($contactSetting?->whatsapp ?? ''));

        if ($whatsappDigits !== '') {
            if (str_starts_with($whatsappDigits, '0')) {
                $whatsappDigits = '98' . substr($whatsappDigits, 1);
            } elseif (! str_starts_with($whatsappDigits, '98')) {
                $whatsappDigits = '98' . $whatsappDigits;
            }
        }
    @endphp

    <div class="fz-contact-page">
        <section class="fz-contact-hero" aria-labelledby="contact-hero-title">
            <div class="fz-contact-hero__grid" aria-hidden="true"></div>
            <div class="fz-contact-hero__glow fz-contact-hero__glow--one" aria-hidden="true"></div>
            <div class="fz-contact-hero__glow fz-contact-hero__glow--two" aria-hidden="true"></div>

            <x-layout.container>
                <nav class="fz-contact-breadcrumb" aria-label="مسیر صفحه">
                    <a href="{{ route('home') }}">خانه</a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">تماس با ما</span>
                </nav>

                <div class="fz-contact-hero__layout">
                    <div class="fz-contact-copy" data-fz-reveal>
                        <p class="fz-contact-eyebrow"><span aria-hidden="true"></span> FARZIN / LET'S CONNECT</p>
                        <h1 id="contact-hero-title" class="fz-contact-title">
                            ارتباط خوب،<br>
                            <span>از یک گفت‌وگو شروع می‌شود.</span>
                        </h1>
                        <p class="fz-contact-lead">
                            برای مشاوره انتخاب هود و سینک، بررسی مشخصات، شرایط خرید یا پیگیری سفارش با تیم فرزین در ارتباط باشید.
                            پیام شما مستقیم به دست ما می‌رسد.
                        </p>

                        <div class="fz-contact-actions">
                            <a href="#contact-form" class="fz-contact-button fz-contact-button--primary">
                                <span>ارسال پیام</span><span aria-hidden="true">↙</span>
                            </a>
                            @if(filled($contactSetting?->phone))
                                <a href="tel:{{ $contactSetting->phone }}" class="fz-contact-button fz-contact-button--glass">
                                    <span>تماس مستقیم</span><span aria-hidden="true">↗</span>
                                </a>
                            @else
                                <a href="{{ route('shop.index') }}" class="fz-contact-button fz-contact-button--glass">
                                    <span>مشاهده محصولات</span><span aria-hidden="true">↗</span>
                                </a>
                            @endif
                        </div>

                        <div class="fz-contact-hero__meta" aria-label="راه‌های ارتباط">
                            <span><i aria-hidden="true"></i> مشاوره محصول</span>
                            <span><i aria-hidden="true"></i> پیگیری سفارش</span>
                            <span><i aria-hidden="true"></i> ارتباط آنلاین</span>
                        </div>
                    </div>

                    <div class="fz-contact-art" data-fz-reveal aria-label="تصویرسازی ارتباط با فرزین">
                        <div class="fz-contact-art__orbit fz-contact-art__orbit--outer" aria-hidden="true"></div>
                        <div class="fz-contact-art__orbit fz-contact-art__orbit--inner" aria-hidden="true"></div>
                        <div class="fz-contact-art__topline"><span>FZ</span><i aria-hidden="true"></i><span>CARE / 01</span></div>
                        <div class="fz-contact-art__device">
                            <div class="fz-contact-art__device-top"><span></span><span></span><span></span></div>
                            <svg viewBox="0 0 360 260" role="img" aria-label="نماد ارتباط با تیم فرزین">
                                <defs>
                                    <linearGradient id="fzContactMetal" x1="30" y1="10" x2="320" y2="250" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#f6fffc"/>
                                        <stop offset=".38" stop-color="#b7d4cb"/>
                                        <stop offset=".68" stop-color="#647b75"/>
                                        <stop offset="1" stop-color="#d5b86f"/>
                                    </linearGradient>
                                    <linearGradient id="fzContactScreen" x1="80" y1="60" x2="280" y2="220" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#172522"/>
                                        <stop offset="1" stop-color="#09110f"/>
                                    </linearGradient>
                                </defs>
                                <circle cx="180" cy="130" r="102" stroke="#9bd8c8" stroke-opacity=".22" stroke-width="1.5" stroke-dasharray="3 8"/>
                                <circle cx="180" cy="130" r="76" stroke="#d5b86f" stroke-opacity=".35"/>
                                <rect x="89" y="40" width="182" height="160" rx="25" fill="url(#fzContactMetal)" fill-opacity=".95" stroke="#eefbf6" stroke-opacity=".7"/>
                                <rect x="101" y="53" width="158" height="133" rx="17" fill="url(#fzContactScreen)"/>
                                <circle cx="180" cy="87" r="20" fill="#0b8c7c" fill-opacity=".28" stroke="#9bd8c8" stroke-opacity=".7"/>
                                <path d="M169 86c0-6 4-10 11-10s11 4 11 10-4 11-11 11-11-5-11-11Z" stroke="#c5f1e1" stroke-width="2" fill="none"/>
                                <path d="M157 116c5-12 15-17 23-17s18 5 23 17" stroke="#c5f1e1" stroke-width="2" stroke-linecap="round" fill="none"/>
                                <path d="M132 142h96M145 155h70" stroke="#9bd8c8" stroke-opacity=".56" stroke-width="5" stroke-linecap="round"/>
                                <path d="M156 201v12m48-12v12M142 214h76" stroke="url(#fzContactMetal)" stroke-width="7" stroke-linecap="round"/>
                                <circle cx="74" cy="81" r="5" fill="#d5b86f"/>
                                <circle cx="286" cy="163" r="5" fill="#9bd8c8"/>
                                <path d="M64 81h-18m250 82h18" stroke="#9bd8c8" stroke-opacity=".65" stroke-linecap="round"/>
                            </svg>
                            <div class="fz-contact-art__device-status"><span></span> ارتباط مستقیم با فرزین</div>
                        </div>
                        <div class="fz-contact-art__floating fz-contact-art__floating--message">
                            <span class="fz-contact-art__floating-icon" aria-hidden="true">↗</span>
                            <span><b>LET'S TALK</b><small>پاسخ‌گویی به پرسش شما</small></span>
                        </div>
                        <div class="fz-contact-art__floating fz-contact-art__floating--pin">
                            <span class="fz-contact-art__pin-dot" aria-hidden="true"></span>
                            <span><b>LOCATION</b><small>نقشه و آدرس مجموعه</small></span>
                        </div>
                    </div>
                </div>
            </x-layout.container>
        </section>

        <section class="fz-contact-workspace" aria-label="راه‌های ارتباط و فرم تماس">
            <x-layout.container>
                <div class="fz-contact-grid">
                    {{-- Right column in RTL: location and map --}}
                    <aside class="fz-contact-map-column" data-fz-reveal>
                        <div class="fz-contact-panel fz-contact-map-panel">
                            <div class="fz-contact-panel__header">
                                <div>
                                    <p class="fz-contact-kicker">FIND FARZIN</p>
                                    <h2>موقعیت ما روی نقشه</h2>
                                    <p>آدرس مجموعه را ببینید و مسیر رسیدن را باز کنید.</p>
                                </div>
                                <span class="fz-contact-panel__badge" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.4" stroke="currentColor" stroke-width="1.7"/></svg>
                                </span>
                            </div>

                            @if($hasMapCoordinates || $mapAddress !== '')
                                <div class="fz-contact-map-frame">
                                    <iframe
                                        title="نقشه موقعیت مجموعه فرزین"
                                        src="https://www.google.com/maps?q={{ urlencode($mapQuery) }}&amp;z=15&amp;output=embed"
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        allowfullscreen
                                    ></iframe>
                                    <span class="fz-contact-map-frame__corner fz-contact-map-frame__corner--one" aria-hidden="true"></span>
                                    <span class="fz-contact-map-frame__corner fz-contact-map-frame__corner--two" aria-hidden="true"></span>
                                </div>
                                <div class="fz-contact-address">
                                    <span class="fz-contact-address__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.4" stroke="currentColor" stroke-width="1.7"/></svg>
                                    </span>
                                    <div>
                                        @if($mapAddress !== '')
                                            <span>آدرس ثبت‌شده</span>
                                            <p>{{ $mapAddress }}</p>
                                        @else
                                            <span>مختصات ثبت‌شده</span>
                                            <p dir="ltr">{{ $mapLatitude }}, {{ $mapLongitude }}</p>
                                        @endif
                                    </div>
                                </div>
                                <a
                                    href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($mapQuery) }}"
                                    class="fz-contact-directions"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >مسیریابی در Google Maps <span aria-hidden="true">↗</span></a>
                            @else
                                <div class="fz-contact-map-empty" role="status">
                                    <div class="fz-contact-map-empty__rings" aria-hidden="true"><span></span><i></i></div>
                                    <strong>هنوز موقعیتی ثبت نشده</strong>
                                    <p>پس از ثبت آدرس یا مختصات مجموعه در تنظیمات فروشگاه پنل مدیریت، نقشه در همین بخش نمایش داده می‌شود.</p>
                                </div>
                                <div class="fz-contact-address fz-contact-address--pending">
                                    <span class="fz-contact-address__icon" aria-hidden="true">⌖</span>
                                    <div><span>موقعیت مجموعه</span><p>آدرس یا مختصات از تنظیمات واقعی فروشگاه خوانده می‌شود.</p></div>
                                </div>
                            @endif

                            @if(filled($contactSetting?->hours))
                                <div class="fz-contact-hours">
                                    <span class="fz-contact-hours__icon" aria-hidden="true">◷</span>
                                    <div><span>ساعات پاسخ‌گویی</span><p>{{ $contactSetting->hours }}</p></div>
                                </div>
                            @endif
                        </div>

                        <div class="fz-contact-map-note">
                            <span class="fz-contact-map-note__pulse" aria-hidden="true"></span>
                            <p>پیش از مراجعه حضوری، برای هماهنگی با تیم فرزین تماس بگیرید.</p>
                        </div>
                    </aside>

                    {{-- Left column in RTL: direct contact, social channels and message form --}}
                    <div class="fz-contact-left-column">
                        <section class="fz-contact-panel fz-contact-channels" data-fz-reveal>
                            <div class="fz-contact-panel__header">
                                <div>
                                    <p class="fz-contact-kicker">DIRECT CHANNELS</p>
                                    <h2>راه‌های ارتباطی</h2>
                                    <p>مسیر مناسب خودتان را انتخاب کنید.</p>
                                </div>
                                <span class="fz-contact-live"><i aria-hidden="true"></i> در دسترس</span>
                            </div>

                            <div class="fz-contact-channel-grid">
                                @if(filled($contactSetting?->phone))
                                    <a class="fz-contact-channel" href="tel:{{ $contactSetting->phone }}">
                                        <span class="fz-contact-channel__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none"><path d="M7 3.8 4.9 5.1c-.8.5-1.1 1.6-.7 2.5 2.4 5.3 6.8 9.7 12.1 12.1.9.4 2 0 2.5-.7l1.3-2.1-4.1-2.5-1.4 1.7a16 16 0 0 1-6.7-6.7l1.7-1.4L7 3.8Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                        <span class="fz-contact-channel__copy"><small>PHONE / تماس تلفنی</small><strong>{{ $contactSetting->phone_label ?: 'تماس مستقیم' }}</strong><em dir="ltr">{{ $contactSetting->phone }}</em></span>
                                        <span class="fz-contact-channel__arrow" aria-hidden="true">↗</span>
                                    </a>
                                @endif

                                @if(filled($contactSetting?->email))
                                    <a class="fz-contact-channel" href="mailto:{{ $contactSetting->email }}">
                                        <span class="fz-contact-channel__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2.5" stroke="currentColor" stroke-width="1.6"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                        <span class="fz-contact-channel__copy"><small>EMAIL / ایمیل</small><strong>{{ $contactSetting->email_label ?: 'ارتباط ایمیلی' }}</strong><em dir="ltr">{{ $contactSetting->email }}</em></span>
                                        <span class="fz-contact-channel__arrow" aria-hidden="true">↗</span>
                                    </a>
                                @endif

                                @if(!filled($contactSetting?->phone) && !filled($contactSetting?->email))
                                    <div class="fz-contact-empty-channel">شماره تماس و ایمیل پس از ثبت در تنظیمات تماس نمایش داده می‌شوند.</div>
                                @endif
                            </div>

                            @if($whatsappDigits !== '' || filled($contactSetting?->instagram_url))
                                <div class="fz-contact-social">
                                    <div class="fz-contact-social__heading">
                                        <span class="fz-contact-kicker">SOCIAL / ONLINE</span>
                                        <h3>در فضای مجازی هم کنار شماییم</h3>
                                    </div>
                                    <div class="fz-contact-social__links">
                                        @if($whatsappDigits !== '')
                                            <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer" class="fz-contact-social__link fz-contact-social__link--whatsapp">
                                                <span aria-hidden="true">W</span> واتساپ <b aria-hidden="true">↗</b>
                                            </a>
                                        @endif
                                        @if(filled($contactSetting?->instagram_url))
                                            <a href="{{ $contactSetting->instagram_url }}" target="_blank" rel="noopener noreferrer" class="fz-contact-social__link fz-contact-social__link--instagram">
                                                <span aria-hidden="true">◎</span> اینستاگرام <b aria-hidden="true">↗</b>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="fz-contact-social fz-contact-social--empty">
                                    <div class="fz-contact-social__heading">
                                        <span class="fz-contact-kicker">SOCIAL / ONLINE</span>
                                        <h3>شبکه‌های اجتماعی فرزین</h3>
                                        <p>لینک‌های واتساپ و اینستاگرام پس از ثبت در تنظیمات تماس همین‌جا قرار می‌گیرند.</p>
                                    </div>
                                </div>
                            @endif
                        </section>

                        <section class="fz-contact-panel fz-contact-form-panel" id="contact-form" data-fz-reveal aria-labelledby="contact-form-title">
                            <div class="fz-contact-panel__header">
                                <div>
                                    <p class="fz-contact-kicker">YOUR MESSAGE / 02</p>
                                    <h2 id="contact-form-title">پیام خود را برای ما بنویسید</h2>
                                    <p>فرم را تکمیل کنید تا بتوانیم دقیق‌تر راهنمایی‌تان کنیم.</p>
                                </div>
                                <span class="fz-contact-form-panel__number" aria-hidden="true">02</span>
                            </div>

                            @if(session('success'))
                                <div class="fz-contact-alert fz-contact-alert--success" role="status">
                                    <span aria-hidden="true">✓</span><p>{{ session('success') }}</p>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="fz-contact-alert fz-contact-alert--error" role="alert">
                                    <strong>لطفاً موارد زیر را بررسی کنید:</strong>
                                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                </div>
                            @endif

                            <form action="{{ route('contact.store') }}" method="POST" class="fz-contact-form">
                                @csrf

                                <div class="fz-contact-form__row">
                                    <div class="fz-contact-field">
                                        <label for="name">نام و نام خانوادگی <span>*</span></label>
                                        <input id="name" name="name" type="text" value="{{ old('name', auth()->user()?->name) }}" autocomplete="name" placeholder="نام شما" required maxlength="120" @error('name') aria-invalid="true" @enderror>
                                        @error('name')<small class="fz-contact-field__error">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="fz-contact-field">
                                        <label for="phone">شماره تماس <span>*</span></label>
                                        <input id="phone" name="phone" type="tel" value="{{ old('phone', auth()->user()?->phone) }}" autocomplete="tel" dir="ltr" placeholder="09xxxxxxxxx" required maxlength="30" @error('phone') aria-invalid="true" @enderror>
                                        @error('phone')<small class="fz-contact-field__error">{{ $message }}</small>@enderror
                                    </div>
                                </div>

                                <div class="fz-contact-field">
                                    <label for="email">ایمیل <small>اختیاری</small></label>
                                    <input id="email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" autocomplete="email" dir="ltr" placeholder="you@example.com" maxlength="255" @error('email') aria-invalid="true" @enderror>
                                    @error('email')<small class="fz-contact-field__error">{{ $message }}</small>@enderror
                                </div>

                                <div class="fz-contact-field">
                                    <label for="subject">موضوع پیام <span>*</span></label>
                                    <select id="subject" name="subject" required @error('subject') aria-invalid="true" @enderror>
                                        <option value="">موضوع را انتخاب کنید</option>
                                        <option value="product" @selected(old('subject') === 'product')>مشاوره درباره محصول</option>
                                        <option value="installment" @selected(old('subject') === 'installment')>شرایط خرید اقساطی</option>
                                        <option value="order" @selected(old('subject') === 'order')>پیگیری سفارش</option>
                                        <option value="shipping" @selected(old('subject') === 'shipping')>ارسال و تحویل</option>
                                        <option value="other" @selected(old('subject') === 'other')>سایر موضوعات</option>
                                    </select>
                                    @error('subject')<small class="fz-contact-field__error">{{ $message }}</small>@enderror
                                </div>

                                <div class="fz-contact-field">
                                    <label for="message">متن پیام <span>*</span></label>
                                    <textarea id="message" name="message" rows="5" required minlength="10" maxlength="5000" placeholder="چطور می‌توانیم کمکتان کنیم؟" @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                                    @error('message')<small class="fz-contact-field__error">{{ $message }}</small>@enderror
                                    <small class="fz-contact-field__hint">حداقل ۱۰ کاراکتر · اطلاعات تماس فقط برای پاسخ‌گویی استفاده می‌شوند.</small>
                                </div>

                                <button type="submit" class="fz-contact-submit">
                                    <span>ارسال پیام به تیم فرزین</span><span aria-hidden="true">↙</span>
                                </button>
                            </form>
                        </section>
                    </div>
                </div>
            </x-layout.container>
        </section>

        <section class="fz-contact-bottom">
            <x-layout.container>
                <div class="fz-contact-bottom__inner" data-fz-reveal>
                    <div>
                        <p class="fz-contact-kicker">NEED PRODUCT GUIDANCE?</p>
                        <h2>برای انتخاب دقیق‌تر، از کاتالوگ شروع کنید.</h2>
                        <p>مدل‌ها و مشخصات هود و سینک را ببینید؛ هر سؤال دیگری داشتید، همین‌جا به ما پیام بدهید.</p>
                    </div>
                    <a href="{{ route('shop.index') }}" class="fz-contact-button fz-contact-button--light">رفتن به فروشگاه <span aria-hidden="true">↗</span></a>
                </div>
            </x-layout.container>
        </section>
    </div>
@endsection
