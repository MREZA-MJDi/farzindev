@extends('layouts.app')

@php
    $aboutCopy = $storefrontPageContent ?? [];
    $aboutText = static fn (string $key, string $fallback): string =>
        filled($aboutCopy[$key] ?? null) ? trim((string) $aboutCopy[$key]) : $fallback;
    $aboutVisible = static fn (string $key): bool =>
        (string) ($aboutCopy[$key] ?? '1') === '1';
@endphp

@section('title', 'درباره فرجی‌هوم | فروشگاه تخصصی تجهیزات آشپزخانه')

@section(
    'description',
    $aboutText('page_about_hero_description', 'با فرجی‌هوم آشنا شوید؛ مهندسی ایمنی صنعتی از سال ۱۳۸۸، تولیدکننده هود آشپزخانه با برندهای بلینی و GIANNO و نماینده و توزیع‌کننده عمده برندهای هادسون و داتیس در تهران - شهریار.')
)

@section('canonical', route('about'))

@push('seo')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "AboutPage",
    "name": "فرجی‌هوم",
    "description": "فروشگاه تخصصی تجهیزات آشپزخانه، مهندسی ایمنی صنعتی از سال ۱۳۸۸، تولیدکننده هود آشپزخانه با برند GIANNO و توزیع‌کننده عمده برندهای هادسون و داتیس",
    "url": @json(url('/')),
    "mainEntity": {
        "@@type": "Organization",
        "name": "فرجی‌هوم",
        "url": @json(url('/')),
        "address": {
            "@@type": "PostalAddress",
            "streetAddress": "تهران - شهریار، سعید آباد، بلوار ۱۵ متری سلمان، نبش کوچه آزادگان",
            "addressLocality": "شهریار",
            "addressRegion": "تهران",
            "addressCountry": "IR"
        }
    },
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

        {{-- =========================================================
             HERO
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-10 sm:py-16 lg:py-20">

                    <nav
                        aria-label="breadcrumb"
                        class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]"
                    >

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-text)]">
                        درباره ما
                    </span>

                    </nav>

                    <div class="mt-10 grid items-end gap-10 lg:grid-cols-[1fr_0.8fr]">

                        <div class="max-w-4xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                                {{ $aboutText('page_about_hero_eyebrow', 'درباره فرجی‌هوم') }}
                            </p>

                            <h1 class="mt-5 text-5xl font-semibold leading-[1.02] tracking-tight sm:text-6xl lg:text-7xl">
                                {{ $aboutText('page_about_hero_title', 'فرجی‌هوم؛') }}
                                <span class="block text-[var(--livora-accent)]">
                                    {{ $aboutText('page_about_hero_highlight', 'همراه انتخاب بهتر تجهیزات آشپزخانه.') }}
                                </span>
                            </h1>

                        </div>

                        <div>

                            <p class="text-sm leading-8 text-[var(--livora-stone)] sm:text-base">
                                {{ $aboutText('page_about_hero_description', 'فرجی‌هوم فروشگاه تخصصی تجهیزات آشپزخانه است؛ فعالیت ما از سال ۱۳۸۸ با تخصص مهندسی ایمنی صنعتی آغاز شد و با تولید هود آشپزخانه با برند بلینی (۱۳۹۵ تا ۱۳۹۹)، عاملیت و توزیع عمده برندهای معتبر داتیس و هادسون، و اکنون تولید نسل جدید هود با برند GIANNO تداوم دارد. تلاش می‌کنیم تصویر، قیمت و مشخصات هر کالا را کنار هم قرار دهیم تا پیش از خرید، انتخاب دقیق‌تری داشته باشید.') }}
                            </p>

                            <div class="mt-7 flex flex-wrap items-center gap-3">
                                <a
                                    href="{{ route('shop.index') }}"
                                    class="inline-flex items-center rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium !text-white transition-colors duration-300 hover:bg-[var(--livora-accent)] hover:!text-white"
                                >
                                    {{ $aboutText('page_about_hero_cta', 'مشاهده محصولات') }}
                                </a>

                                <a
                                    href="#location"
                                    class="inline-flex items-center rounded-2xl border border-[var(--livora-border)] bg-transparent px-6 py-4 text-sm font-medium text-[var(--livora-text)] transition-colors duration-300 hover:border-[var(--livora-ink)]"
                                >
                                    نشانی و موقعیت حضوری
                                </a>
                            </div>
                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             CAREER & EXPERIENCE MILESTONES (درباره من / سوابق تخصصی)
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-14 sm:py-16 lg:py-20">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                                پیشینه و ارکان تخصصی
                            </p>
                            <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                از مهندسی ایمنی صنعتی تا تولید اختصاصی و توزیع کشوری
                            </h2>
                        </div>
                        <p class="max-w-md text-xs leading-6 text-[var(--livora-stone)] sm:text-sm">
                            بیش از یک دهه تجربه مستمر در حوزه استانداردهای مهندسی، تولید صنعتی هود و نمایندگی برندهای ممتاز آشپزخانه
                        </p>
                    </div>

                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                        {{-- Milestone 1 --}}
                        <div class="group relative flex flex-col justify-between rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-6 transition-all duration-300 hover:border-[var(--livora-accent)]/50 hover:shadow-sm">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center rounded-xl bg-[var(--livora-ink)] px-3 py-1 text-xs font-bold text-white">
                                        از سال ۱۳۸۸
                                    </span>
                                    <span class="text-xl" aria-hidden="true">🛡️</span>
                                </div>
                                <h3 class="mt-5 text-base font-bold text-[var(--livora-text)]">
                                    مهندس ایمنی صنعتی
                                </h3>
                                <p class="mt-2.5 text-xs leading-6 text-[var(--livora-stone)]">
                                    آغاز فعالیت حرفه‌ای با تکیه بر دانش ایمنی صنعتی، ارزیابی استانداردهای فنی، ارگونومی و نظارت دقیق بر سلامت و ایمنی تجهیزات حرارتی و تهویه.
                                </p>
                            </div>
                            <div class="mt-6 border-t border-[var(--livora-border)] pt-3 text-[11px] font-medium text-[var(--livora-accent)]">
                                مبنای استانداردهای فنی و ایمنی
                            </div>
                        </div>

                        {{-- Milestone 2 --}}
                        <div class="group relative flex flex-col justify-between rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-6 transition-all duration-300 hover:border-[var(--livora-accent)]/50 hover:shadow-sm">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center rounded-xl bg-[var(--livora-ink)] px-3 py-1 text-xs font-bold text-white">
                                        ۱۳۹۵ تا ۱۳۹۹
                                    </span>
                                    <span class="text-xl" aria-hidden="true">🏭</span>
                                </div>
                                <h3 class="mt-5 text-base font-bold text-[var(--livora-text)]">
                                    تولید هود با برند بلینی
                                </h3>
                                <p class="mt-2.5 text-xs leading-6 text-[var(--livora-stone)]">
                                    ۴ سال تجربه درخشان در خط تولید صنعتی هود آشپزخانه با برند «بلینی» (Bellini)؛ قالب‌سازی دقیق، انتخاب موتورهای مرغوب و شناخت عمیق نیازهای بازار.
                                </p>
                            </div>
                            <div class="mt-6 border-t border-[var(--livora-border)] pt-3 text-[11px] font-medium text-[var(--livora-accent)]">
                                ۴ سال تولید مستمر صنعتی
                            </div>
                        </div>

                        {{-- Milestone 3 --}}
                        <div class="group relative flex flex-col justify-between rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-6 transition-all duration-300 hover:border-[var(--livora-accent)]/50 hover:shadow-sm">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center rounded-xl bg-[var(--livora-ink)] px-3 py-1 text-xs font-bold text-white">
                                        توزیع و عاملیت
                                    </span>
                                    <span class="text-xl" aria-hidden="true">🤝</span>
                                </div>
                                <h3 class="mt-5 text-base font-bold text-[var(--livora-text)]">
                                    نمایندگی هادسون و داتیس
                                </h3>
                                <p class="mt-2.5 text-xs leading-6 text-[var(--livora-stone)]">
                                    توزیع‌کننده عمده و نماینده رسمی فروش محصولات برندهای معتبر کشوری «هادسون» (Hadson) و «داتیس» (Datees) با تضمین اصالت و زنجیره تأمین بدون واسطه.
                                </p>
                            </div>
                            <div class="mt-6 border-t border-[var(--livora-border)] pt-3 text-[11px] font-medium text-[var(--livora-accent)]">
                                عاملیت رسمی و توزیع سراسری
                            </div>
                        </div>

                        {{-- Milestone 4 --}}
                        <div class="group relative flex flex-col justify-between rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-6 transition-all duration-300 hover:border-[var(--livora-accent)]/50 hover:shadow-sm">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center rounded-xl bg-[var(--livora-accent)] px-3 py-1 text-xs font-bold text-white">
                                        تولید اختصاصی
                                    </span>
                                    <span class="text-xl" aria-hidden="true">⭐</span>
                                </div>
                                <h3 class="mt-5 text-base font-bold text-[var(--livora-text)]">
                                    تولید هود با برند GIANNO
                                </h3>
                                <p class="mt-2.5 text-xs leading-6 text-[var(--livora-stone)]">
                                    تولیدکننده نسل جدید هودهای لوکس آشپزخانه با برند «GIANNO»؛ تلفیق طراحی مدرن، موتورهای توربو کم‌صدا، مکش بالا و دوام ماندگار قطعات.
                                </p>
                            </div>
                            <div class="mt-6 border-t border-[var(--livora-border)] pt-3 text-[11px] font-medium text-[var(--livora-accent)]">
                                تولید اختصاصی هود مدرن
                            </div>
                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             WORKSHOP & FACTORY GALLERY (تصاویر کارگاه و خط تولید فرجی‌هوم)
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-gradient-to-b from-[var(--livora-surface)] to-[var(--livora-cream)]/40">

            <x-layout.container>

                <div class="py-14 sm:py-16 lg:py-20">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-xl bg-[var(--livora-white)] px-3 py-1.5 text-xs font-semibold text-[var(--livora-accent)] shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-[var(--livora-accent)]"></span>
                                <span>کارگاه اختصاصی و خط تولید</span>
                            </div>
                            <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl">
                                خط تولید و کارگاه ساخت هودهای آشپزخانه
                            </h2>
                        </div>
                        <p class="max-w-md text-xs leading-6 text-[var(--livora-stone)] sm:text-sm">
                            نگاهی مستقیم به محیط کارگاه، مراحل مونتاژ صنعتی، قطعات استاندارد و ردیف‌های تولید هود با برند اختصاصی GIANNO در مجموعه فرجی‌هوم (سعید آباد شهریار)
                        </p>
                    </div>

                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">

                        {{-- Photo 1: سوله و خط مونتاژ --}}
                        <div class="group overflow-hidden rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                            <div class="relative aspect-[3/4] overflow-hidden bg-slate-100">
                                <img
                                    src="{{ asset('images/about/factory-production-hall.jpg') }}"
                                    alt="سوله و سالن مونتاژ هودهای آشپزخانه فرجی‌هوم"
                                    loading="lazy"
                                    class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                                <span class="absolute right-4 top-4 rounded-xl bg-black/60 px-3 py-1 text-[11px] font-bold text-white backdrop-blur-md" style="color: #ffffff !important;">
                                    مرحله ۱ · سالن مونتاژ
                                </span>
                                <div class="absolute bottom-4 right-4 left-4 z-10 text-white">
                                    <h3 class="text-base font-bold sm:text-lg !text-white drop-shadow-md" style="color: #ffffff !important;">سوله و خط مونتاژ صنعتی</h3>
                                    <p class="mt-1 text-xs font-medium !text-white drop-shadow-sm line-clamp-2" style="color: #ffffff !important;">ساخت و تجمیع شاسی و بدنه هود با رعایت کامل اصول مهندسی ایمنی صنعتی</p>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center justify-between text-xs text-[var(--livora-stone)]">
                                    <span>نظارت بر استاندارد تولید</span>
                                    <span class="font-bold text-[var(--livora-accent)]">مهندسی ایمنی از ۱۳۸۸</span>
                                </div>
                            </div>
                        </div>

                        {{-- Photo 2: فیلترها و موتورهای حلزونی --}}
                        <div class="group overflow-hidden rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                            <div class="relative aspect-[3/4] overflow-hidden bg-slate-100">
                                <img
                                    src="{{ asset('images/about/factory-assembly-line.jpg') }}"
                                    alt="خط تجمیع فیلترهای آلومینیومی و موتورهای پرقدرت هود"
                                    loading="lazy"
                                    class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                                <span class="absolute right-4 top-4 rounded-xl bg-black/60 px-3 py-1 text-[11px] font-bold text-white backdrop-blur-md" style="color: #ffffff !important;">
                                    مرحله ۲ · قطعات و موتورها
                                </span>
                                <div class="absolute bottom-4 right-4 left-4 z-10 text-white">
                                    <h3 class="text-base font-bold sm:text-lg !text-white drop-shadow-md" style="color: #ffffff !important;">فیلترهای آلومینیومی و فن‌های فلزی</h3>
                                    <p class="mt-1 text-xs font-medium !text-white drop-shadow-sm line-clamp-2" style="color: #ffffff !important;">به‌کارگیری فیلترهای آلومینیومی قابل شستشو و موتورهای توربو کم‌صدا با مکش بالا</p>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center justify-between text-xs text-[var(--livora-stone)]">
                                    <span>قطعات فلزی با دوام بالا</span>
                                    <span class="font-bold text-[var(--livora-accent)]">موتور توربو ۴ دور</span>
                                </div>
                            </div>
                        </div>

                        {{-- Photo 3: هودهای آماده برند GIANNO --}}
                        <div class="group overflow-hidden rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md sm:col-span-2 lg:col-span-1">
                            <div class="relative aspect-[3/4] overflow-hidden bg-slate-100">
                                <img
                                    src="{{ asset('images/about/factory-gianno-hoods.jpg') }}"
                                    alt="هودهای آشپزخانه شیشه سکوریت برند GIANNO آماده ارسال"
                                    loading="lazy"
                                    class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                                <span class="absolute right-4 top-4 rounded-xl bg-[var(--livora-accent)] px-3 py-1 text-[11px] font-bold text-white shadow-sm" style="color: #ffffff !important;">
                                    مرحله ۳ · محصول نهایی GIANNO
                                </span>
                                <div class="absolute bottom-4 right-4 left-4 z-10 text-white">
                                    <h3 class="text-base font-bold sm:text-lg !text-white drop-shadow-md" style="color: #ffffff !important;">هودهای تولیدی برند GIANNO</h3>
                                    <p class="mt-1 text-xs font-medium !text-white drop-shadow-sm line-clamp-2" style="color: #ffffff !important;">شیشه‌های سکوریت مقاوم، کلید لمسی، کنترل کیفی نهایی و آماده‌سازی برای تحویل</p>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center justify-between text-xs text-[var(--livora-stone)]">
                                    <span>تست سلامت و کنترل کیفیت</span>
                                    <span class="font-bold text-[var(--livora-accent)]">برند اختصاصی GIANNO</span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


   


        {{-- =========================================================
             BRAND STATEMENT
        ========================================================== --}}
        @if($aboutVisible('page_about_show_statement'))
<section class="border-b border-[var(--livora-border)]">

            <x-layout.container>

                <div class="grid gap-10 py-16 sm:py-20 lg:grid-cols-[0.7fr_1.3fr] lg:py-24">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            {{ $aboutText('page_about_statement_eyebrow', 'نگاه ما به انتخاب محصول') }}
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            {{ $aboutText('page_about_statement_title', 'انتخاب خوب، از اطلاعات روشن شروع می‌شود.') }}
                        </h2>

                    </div>

                    <div class="max-w-3xl">

                        <p class="text-lg leading-9 text-[var(--livora-text)] sm:text-xl">
                            {{ $aboutText('page_about_statement_body_1', 'در فرجی‌هوم، پیشینه مهندسی ایمنی صنعتی از سال ۱۳۸۸ و سال‌ها تجربه ساخت هود آشپزخانه با برندهای بلینی و GIANNO در کنار نمایندگی رسمی برندهای هادسون و داتیس قرار گرفته تا خریدار با آگاهی کامل از مشخصات فنی، ایمنی و ابعاد استاندارد انتخاب کند.') }}
                        </p>

                        <p class="mt-6 text-sm leading-8 text-[var(--livora-stone)]">
                            {{ $aboutText('page_about_statement_body_2', 'ما تلفیقی از تجربه تولید صنعتی و عاملیت فروش برترین برندهای بازار را با دسته‌بندی روشن و پشتیبانی مستقیم به همراه نشانی حضوری (شهریار، سعید آباد) در اختیار مشتریان گرامی قرار می‌دهیم.') }}
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>
@endif


        {{-- =========================================================
             FACTORY AND WORK
        ========================================================== --}}
        @if($aboutVisible('page_about_show_factory'))
<section class="relative overflow-hidden bg-[var(--livora-ink)] text-white">
            <div aria-hidden="true" class="pointer-events-none absolute -right-24 top-0 h-72 w-72 rounded-full bg-[var(--livora-accent)]/20 blur-3xl"></div>
            <x-layout.container>
                <div class="relative grid gap-10 py-16 sm:py-20 lg:grid-cols-[0.85fr_1.15fr] lg:py-24">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-[#b7c9cb]">{{ $aboutText('page_about_factory_eyebrow', 'از جستجو تا انتخاب') }}</p>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">
                            {{ $aboutText('page_about_factory_title', 'چطور انتخاب را ساده‌تر می‌کنیم؟') }}
                        </h2>
                    </div>
                    <div class="max-w-3xl">
                        <p class="text-base leading-9 text-white/80 sm:text-lg">
                            {{ $aboutText('page_about_factory_body_1', 'برای هود، نوع نصب، عرض، قدرت مکش و میزان صدا را بررسی کنید؛ برای اجاق گاز، ابعاد برش، تعداد شعله و امکانات ایمنی؛ و برای سینک، روش نصب، تعداد لگن، ابعاد و جنس. این معیارها کمک می‌کنند محصولات را با فضای آشپزخانه خود تطبیق دهید.') }}
                        </p>
                        <p class="mt-5 text-sm leading-8 text-white/60">
                            {{ $aboutText('page_about_factory_body_2', 'مشخصات فنی، قیمت و وضعیت موجودی هر مدل را در صفحه همان محصول بررسی کنید. در صورت وجود ابهام، پیش از ثبت سفارش از راه‌های ارتباطی فروشگاه سؤال کنید؛ ویژگی‌های هر کالا باید بر اساس اطلاعات ثبت‌شده همان مدل سنجیده شود.') }}
                        </p>
                        <a href="{{ route('shop.index') }}" class="mt-7 inline-flex items-center gap-3 rounded-xl bg-[var(--livora-accent)] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-text)]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0" aria-hidden="true"><path d="M3.5 9h17l-1 11h-15l-1-11Z" /><path d="M8 9V6.5a4 4 0 0 1 8 0V9" /></svg> {{ $aboutText('page_about_factory_cta', 'مشاهده محصولات') }}
                        </a>
                    </div>
                </div>
            </x-layout.container>
        </section>
@endif

        {{-- =========================================================
             VALUES
        ========================================================== --}}
        @if($aboutVisible('page_about_show_values'))
<section class="bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="py-16 sm:py-20 lg:py-24">

                    <div class="max-w-2xl">

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
{{ $aboutText('page_about_values_eyebrow', 'OUR FOCUS') }}
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            {{ $aboutText('page_about_values_title', 'خریدی که با نیاز شما شروع می‌شود.') }}
                        </h2>

                    </div>

                    <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4">

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            01
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                {{ $aboutText('page_about_value_1_title', 'مهندسی ایمنی (از ۱۳۸۸)') }}
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $aboutText('page_about_value_1_description', 'به‌کارگیری استانداردهای مهندسی ایمنی صنعتی در بررسی، تولید و عرضه تجهیزات آشپزخانه.') }}
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            02
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                {{ $aboutText('page_about_value_2_title', 'تولید هود بلینی (۱۳۹۵ تا ۱۳۹۹)') }}
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $aboutText('page_about_value_2_description', 'سابقه ۴ سال تولید مستمر هود آشپزخانه تحت برند بلینی و تسلط عمیق بر قطعات و خط تولید.') }}
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            03
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                {{ $aboutText('page_about_value_3_title', 'توزیع عمده هادسون و داتیس') }}
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $aboutText('page_about_value_3_description', 'توزیع عمده و عاملیت رسمی فروش معتبرترین برندهای لوازم خانگی هادسون و داتیس با ضمانت اصالت.') }}
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            04
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                {{ $aboutText('page_about_value_4_title', 'تولیدکننده برند GIANNO') }}
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $aboutText('page_about_value_4_description', 'تولید نسل جدید هودهای لوکس و پرقدرت آشپزخانه با برند اختصاصی GIANNO و بالاترین کیفیت ساخت.') }}
                            </p>

                        </article>

                    </div>

                </div>

            </x-layout.container>

        </section>
@endif


        {{-- =========================================================
             PROCESS
        ========================================================== --}}
        @if($aboutVisible('page_about_show_experience'))
<section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="grid gap-10 py-16 sm:py-20 lg:grid-cols-[0.65fr_1.35fr] lg:py-24">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            {{ $aboutText('page_about_experience_eyebrow', 'THE EXPERIENCE') }}
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            {{ $aboutText('page_about_experience_title', 'از کشف تا انتخاب.') }}
                        </h2>

                        <p class="mt-4 text-sm leading-8 text-[var(--livora-stone)]">
                            {{ $aboutText('page_about_experience_description', 'خرید از فرجی‌هوم از انتخاب دسته و مقایسه مدل‌ها تا ثبت و پیگیری سفارش را در یک مسیر روشن فراهم می‌کند.') }}
                        </p>

                    </div>

                    <div class="space-y-3">

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                01
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        {{ $aboutText('page_about_step_1_title', 'کشف') }}
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        {{ $aboutText('page_about_step_1_description', 'دسته‌بندی‌ها و مجموعه‌های مختلف را بررسی می‌کنید و محصول مناسب فضای خود را پیدا می‌کنید.') }}
                                    </p>

                                </div>

                            </div>

                        </article>

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                02
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        {{ $aboutText('page_about_step_2_title', 'بررسی') }}
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        {{ $aboutText('page_about_step_2_description', 'قیمت، مشخصات، موجودی و در صورت وجود، شرایط خرید اقساطی محصول را می‌بینید.') }}
                                    </p>

                                </div>

                            </div>

                        </article>

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                03
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        {{ $aboutText('page_about_step_3_title', 'انتخاب') }}
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        {{ $aboutText('page_about_step_3_description', 'محصول و ویژگی‌های موردنظر را انتخاب و وارد سبد خرید می‌کنید.') }}
                                    </p>

                                </div>

                            </div>

                        </article>

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                04
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        {{ $aboutText('page_about_step_4_title', 'پرداخت') }}
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        {{ $aboutText('page_about_step_4_description', 'روش پرداخت قابل استفاده برای سفارش، پیش از نهایی‌کردن خرید به‌صورت روشن اعلام می‌شود.') }}
                                    </p>

                                </div>

                            </div>

                        </article>

                    </div>

                </div>

            </x-layout.container>

        </section>
@endif


        {{-- =========================================================
             INSTALLMENT
        ========================================================== --}}
        @if($aboutVisible('page_about_show_installment'))
<section class="border-t border-white/10 bg-[var(--livora-ink)] text-white">

            <x-layout.container>

                <div class="grid gap-10 py-16 sm:py-20 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:py-24">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-white/45">
                            {{ $aboutText('page_about_installment_eyebrow', 'FLEXIBLE PAYMENT') }}
                        </p>

                        <h2 class="mt-4 max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl">
                            {{ $aboutText('page_about_installment_title', 'روش پرداخت متناسب با شرایط هر محصول را بررسی کنید.') }}
                        </h2>

                        <p class="mt-5 max-w-2xl text-sm leading-8 text-white/55">
                            {{ $aboutText('page_about_installment_description', 'شرایط اقساطی، در صورت فعال بودن برای محصول، همراه با درصد پیش‌پرداخت، تعداد چک و فاصله سررسید نمایش داده می‌شود.') }}
                        </p>

                        <a
                            href="{{ route('shop.index') }}"
                            class="mt-8 inline-flex items-center rounded-2xl  px-6 py-4 text-sm font-medium text-[var(--livora-text)] transition hover:bg-[var(--livora-cream)]"
                        >
                            {{ $aboutText('page_about_installment_cta', 'مشاهده محصولات') }}
                        </a>

                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-6 sm:p-8">
                        <span class="text-[10px] font-semibold tracking-[0.08em] text-white/55">{{ $aboutText('page_about_installment_card_eyebrow', 'شرایط خرید هر محصول') }}</span>
                        <h3 class="mt-4 text-xl font-semibold">{{ $aboutText('page_about_installment_card_title', 'شرایط پرداخت، متناسب با همان مدل') }}</h3>
                        <p class="mt-3 max-w-2xl text-sm leading-8 text-white/70">
                            {{ $aboutText('page_about_installment_card_description', 'اگر خرید اقساطی برای محصولی فعال باشد، جزئیات همان مدل در صفحه محصول و هنگام ثبت سفارش نمایش داده می‌شود. پیش از نهایی‌کردن خرید، شرایط اعلام‌شده را بررسی کنید.') }}
                        </p>
                        <a href="{{ route('shop.index') }}" class="mt-6 inline-flex items-center rounded-none border border-white/25  px-5 py-3 text-sm font-semibold text-[var(--livora-ink)] transition hover:bg-[var(--livora-cream)]">
                            {{ $aboutText('page_about_installment_card_cta', 'بررسی محصولات') }}
                        </a>
                    </div>

                </div>

            </x-layout.container>

        </section>
@endif


        {{-- =========================================================
             HEADQUARTERS & ADDRESS (دفتر و نشانی مجموعه)
        ========================================================== --}}
        <section id="location" class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-16 sm:py-20 lg:py-24">

                    <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">

                        <div>

                            <div class="inline-flex items-center gap-2 rounded-xl bg-[var(--livora-surface)] px-3 py-1.5 text-xs font-semibold text-[var(--livora-accent)]">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <span>نشانی و مراجعه حضوری</span>
                            </div>

                            <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl text-[var(--livora-text)]">
                                دفتر و انبار مرکزی مجموعه
                            </h2>

                            <p class="mt-4 text-sm leading-8 text-[var(--livora-stone)]">
                                برای هماهنگی سفارش‌های عمده و تکی، استعلام موجودی یا بازدید و مشاوره تخصصی، در مجموعه فرجی‌هوم پاسخگوی شما هستیم.
                            </p>

                            <div class="mt-7 flex flex-wrap items-center gap-3">

                                <a
                                    href="{{ route('contact') }}"
                                    class="inline-flex items-center gap-2 rounded-xl bg-[var(--livora-ink)] px-5 py-3.5 text-sm font-semibold !text-white transition hover:bg-[var(--livora-accent)]"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z" />
                                    </svg>
                                    تماس و هماهنگی
                                </a>

                                <a
                                    href="https://maps.google.com/maps?q=35.653810,51.185191&amp;ll=35.653810,51.185191&amp;z=16"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-5 py-3.5 text-sm font-semibold text-[var(--livora-text)] transition hover:border-[var(--livora-ink)]"
                                >
                                    <span>موقعیت دقیق در گوگل مپ</span>
                                    <span aria-hidden="true">↗</span>
                                </a>

                                <a
                                    href="https://neshan.org/maps/@35.653810,51.185191,16z"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3.5 text-xs font-semibold text-[var(--livora-text)] transition hover:border-[var(--livora-ink)]"
                                >
                                    <span>نشان</span>
                                    <span aria-hidden="true">↗</span>
                                </a>

                                <a
                                    href="https://balad.ir/location?latitude=35.653810&amp;longitude=51.185191"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3.5 text-xs font-semibold text-[var(--livora-text)] transition hover:border-[var(--livora-ink)]"
                                >
                                    <span>بلد</span>
                                    <span aria-hidden="true">↗</span>
                                </a>

                            </div>

                            {{-- شماره‌های تماس مستقیم و اینستاگرام --}}
                            <div class="mt-8 grid gap-2.5 sm:grid-cols-2">
                                <a href="tel:+989126594776" class="flex items-center gap-3 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-3.5 transition hover:border-[var(--livora-accent)]">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-ink)] text-xs text-white">📞</span>
                                    <div class="text-xs">
                                        <span class="block text-[10px] text-[var(--livora-stone)]">مدیریت (فرجی)</span>
                                        <strong class="font-bold text-[var(--livora-text)]" dir="ltr">۰۹۱۲۶۵۹۴۷۷۶</strong>
                                    </div>
                                </a>

                                <a href="tel:+989128919597" class="flex items-center gap-3 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-3.5 transition hover:border-[var(--livora-accent)]">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-ink)] text-xs text-white">📞</span>
                                    <div class="text-xs">
                                        <span class="block text-[10px] text-[var(--livora-stone)]">مدیریت (خط دوم)</span>
                                        <strong class="font-bold text-[var(--livora-text)]" dir="ltr">۰۹۱۲۸۹۱۹۵۹۷</strong>
                                    </div>
                                </a>

                                <a href="tel:+989353411774" class="flex items-center gap-3 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-3.5 transition hover:border-[var(--livora-accent)]">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-accent)] text-xs text-white">👔</span>
                                    <div class="text-xs">
                                        <span class="block text-[10px] text-[var(--livora-stone)]">مدیر فروش</span>
                                        <strong class="font-bold text-[var(--livora-text)]" dir="ltr">۰۹۳۵۳۴۱۱۷۷۴</strong>
                                    </div>
                                </a>

                                <a href="https://instagram.com/Faraji_home110" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-3.5 transition hover:border-[var(--livora-accent)]">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-xs text-white">◎</span>
                                    <div class="text-xs">
                                        <span class="block text-[10px] text-[var(--livora-stone)]">اینستاگرام رسمی</span>
                                        <strong class="font-bold text-[var(--livora-text)]" dir="ltr">@Faraji_home110</strong>
                                    </div>
                                </a>
                            </div>

                        </div>

                        <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-7 sm:p-9">

                            <div class="flex items-start gap-4">

                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-ink)] text-white text-lg" aria-hidden="true">
                                    📍
                                </span>

                                <div>

                                    <span class="text-xs font-semibold text-[var(--livora-accent)] uppercase tracking-wider">نشانی دقیق دفتر و مجموعه</span>

                                    <h3 class="mt-1 text-lg font-bold text-[var(--livora-text)] sm:text-xl">
                                        تهران - شهریار، سعید آباد، بلوار ۱۵ متری سلمان، نبش کوچه آزادگان
                                    </h3>

                                    <p class="mt-2 text-xs leading-6 text-[var(--livora-stone)] sm:text-sm">
                                        موقعیت مکانی دقیق روی نقشه ثبت شده است؛ مجهز به درگاه پرداخت رسمی زرین‌پال و ارسال مستقیم کالا به سراسر ایران.
                                    </p>

                                </div>

                            </div>

                            {{-- نقشه تعاملی دقیق با مختصات ثبت‌شده --}}
                            <div class="mt-5 overflow-hidden rounded-2xl border border-[var(--livora-border)] shadow-inner">
                                <iframe
                                    title="نقشه موقعیت جغرافیایی فرجی‌هوم"
                                    src="https://www.google.com/maps?q=35.653810,51.185191&amp;z=16&amp;output=embed"
                                    class="h-56 w-full border-0"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    allowfullscreen
                                ></iframe>
                            </div>

                            <div class="mt-6 grid gap-3 border-t border-[var(--livora-border)] pt-6 sm:grid-cols-3">

                                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-3.5">
                                    <span class="block text-xs font-bold text-[var(--livora-text)]">هود آشپزخانه</span>
                                    <p class="mt-1 text-[11px] leading-5 text-[var(--livora-stone)]">
                                        برند <strong>GIANNO</strong><br>برند <strong>SABAS</strong>
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-3.5">
                                    <span class="block text-xs font-bold text-[var(--livora-text)]">اجاق گاز صفحه‌ای</span>
                                    <p class="mt-1 text-[11px] leading-5 text-[var(--livora-stone)]">
                                        برند <strong>SABAS</strong><br>طراحی مدرن و ایمن
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-3.5">
                                    <span class="block text-xs font-bold text-[var(--livora-text)]">سینک باکسی</span>
                                    <p class="mt-1 text-[11px] leading-5 text-[var(--livora-stone)]">
                                        برند <strong>GIANNO</strong><br>استیل ضخیم و ضدخش
                                    </p>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FINAL STATEMENT
        ========================================================== --}}
        @if($aboutVisible('page_about_show_final'))
<section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="py-20 sm:py-28">

                    <div class="mx-auto max-w-3xl text-center">

                        <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                            {{ $aboutText('page_about_final_eyebrow', 'فرجی‌هوم') }}
                        </p>

                        <h2 class="mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">
                            {{ $aboutText('page_about_final_title', 'برای انتخاب بهتر تجهیزات آشپزخانه، از همین‌جا شروع کنید.') }}
                        </h2>

                        <p class="mx-auto mt-5 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                            {{ $aboutText('page_about_final_description', 'دسته‌ها و محصولات موجود فرجی‌هوم را بررسی کنید یا برای پرسش پیش از خرید با ما در ارتباط باشید.') }}
                        </p>

                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">

                            <a
                                href="{{ route('shop.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                {{ $aboutText('page_about_final_shop_cta', 'ورود به فروشگاه') }}
                            </a>

                            <a
                                href="{{ route('contact') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-7 py-4 text-sm font-medium text-[var(--livora-text)] transition hover:border-[var(--livora-ink)]"
                            >
                                {{ $aboutText('page_about_final_contact_cta', 'تماس با ما') }}
                            </a>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>
@endif

    </div>

@endsection
