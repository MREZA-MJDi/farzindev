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
    $aboutText('page_about_hero_description', 'با فرجی‌هوم آشنا شوید؛ فروشگاه تخصصی تجهیزات آشپزخانه برای مشاهده و مقایسه هود، اجاق گاز، سینک و محصولات برندهای متنوع.')
)

@section('canonical', route('about'))

@push('seo')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "AboutPage",
    "name": "فرجی‌هوم",
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
                                {{ $aboutText('page_about_hero_description', 'فرجی‌هوم فروشگاه تخصصی تجهیزات آشپزخانه است؛ جایی برای مشاهده و مقایسه مدل‌های هود، اجاق گاز صفحه‌ای، سینک و سایر محصولات موجود از برندهای متنوع. تلاش می‌کنیم تصویر، قیمت و مشخصات هر کالا را کنار هم قرار دهیم تا پیش از خرید، انتخاب دقیق‌تری داشته باشید.') }}
                            </p>

                            <a
                                href="{{ route('shop.index') }}"
                                class="mt-7 inline-flex items-center rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium !text-white transition-colors duration-300 hover:bg-[var(--livora-accent)] hover:!text-white"
                            >
                                {{ $aboutText('page_about_hero_cta', 'مشاهده محصولات') }}
                            </a>
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
                            {{ $aboutText('page_about_statement_body_1', 'در فرجی‌هوم، تصویر و نام محصول نقطه شروع‌اند؛ ابعاد، کاربرد و مشخصات فنی هر مدل نیز در دسترس خریدار قرار می‌گیرد. اطلاعات روشن، انتخابی متناسب با فضای واقعی آشپزخانه را ساده‌تر می‌کند.') }}
                        </p>

                        <p class="mt-6 text-sm leading-8 text-[var(--livora-stone)]">
                            {{ $aboutText('page_about_statement_body_2', 'فرجی‌هوم محصولات برندهای گوناگون را در یک مسیر خرید آنلاین گرد هم می‌آورد؛ با دسته‌بندی روشن، صفحه اختصاصی هر محصول و راه‌های ارتباطی برای پرسش پیش از خرید.') }}
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
                <div class="relative grid gap-10 py-16 sm:py-20 lg:grid-cols-[0.85fr_1.15fr] lg:items-end lg:py-24">
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
                                {{ $aboutText('page_about_value_1_title', 'کاربرد') }}
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $aboutText('page_about_value_1_description', 'فرم و ظاهر محصول را با فضای آشپزخانه و نیاز روزمره خود هماهنگ کنید.') }}
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            02
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                {{ $aboutText('page_about_value_2_title', 'مشخصات روشن') }}
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $aboutText('page_about_value_2_description', 'انتخاب متریال و ساخت خوب، چیزی است که ارزش یک محصول را در طول زمان حفظ می‌کند.') }}
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            03
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                {{ $aboutText('page_about_value_3_title', 'انتخاب مناسب') }}
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $aboutText('page_about_value_3_description', 'قیمت، مشخصات، موجودی و روش‌های پرداخت در صفحه محصول نمایش داده می‌شوند.') }}
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            04
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                {{ $aboutText('page_about_value_4_title', 'پشتیبانی پیش از خرید') }}
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $aboutText('page_about_value_4_description', 'مسیر مشاهده محصول تا ثبت و پیگیری سفارش را ساده و روشن نگه می‌داریم.') }}
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
                            class="mt-8 inline-flex items-center rounded-2xl bg-[var(--livora-white)] px-6 py-4 text-sm font-medium text-[var(--livora-text)] transition hover:bg-[var(--livora-cream)]"
                        >
                            {{ $aboutText('page_about_installment_cta', 'مشاهده محصولات') }}
                        </a>

                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-6 sm:p-8">
                        <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/45">PRODUCT-SPECIFIC TERMS</span>
                        <h3 class="mt-4 text-xl font-semibold">شرایط اقساطی هر محصول، جداگانه و شفاف</h3>
                        <p class="mt-3 max-w-2xl text-sm leading-8 text-white/65">
                            اگر برای محصولی خرید اقساطی فعال باشد، درصد پیش‌پرداخت، تعداد چک و فاصله سررسید در صفحه همان محصول نمایش داده می‌شود. شرایط را پیش از افزودن به سبد بررسی کنید.
                        </p>
                        <a href="{{ route('shop.index') }}" class="mt-6 inline-flex items-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-[var(--livora-ink)] transition hover:bg-[var(--livora-cream)]">
                            مشاهده محصولات
                        </a>
                    </div>

                </div>

            </x-layout.container>

        </section>
@endif


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
