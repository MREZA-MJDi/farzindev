@extends('layouts.app')

@section('title', 'فرزین | خرید هود و سینک آشپزخانه')

@section(
    'description',
    'فرزین؛ مرجع انتخاب هود آشپزخانه و سینک ظرفشویی. محصولات را بر اساس فضای نصب، نیاز و مشخصات فنی مقایسه کنید.'
)

@section('canonical', url('/'))

@push('seo')

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="فرزین | هود و سینک آشپزخانه"
    >

    <meta
        property="og:description"
        content="مدل‌های هود و سینک فرزین را ببینید و مشخصات هر محصول را پیش از خرید بررسی کنید."
    >

    <meta
        property="og:url"
        content="{{ url('/') }}"
    >

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="فرزین | هود و سینک آشپزخانه"
    >

    <meta
        name="twitter:description"
        content="مدل‌های هود و سینک فرزین را ببینید و مشخصات هر محصول را پیش از خرید بررسی کنید."
    >

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "Farzin",
        "url": @json(url('/')),
        "potentialAction": {
            "@@type": "SearchAction",
            "target": @json(url('/shop') . '?search={search_term_string}'),
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "Farzin",
        "url": @json(url('/'))
        }
</script>

@endpush


@section('content')

    @php

        /*
        |--------------------------------------------------------------------------
        | Homepage Data
        |--------------------------------------------------------------------------
        */

        $heroProducts = $featuredProducts
            ->filter(
                fn ($product) =>
                    $product->images?->first()?->url
            )
            ->values();

        $heroProduct = $heroProducts->first();

        /*
         * Keep all featured products in the collection section.
         */
        $featuredWithoutHero = $featuredProducts;

        $installmentProducts = $featuredProducts
            ->filter(
                fn ($product) =>
                    (bool) $product->installment_enabled
            )
            ->take(4);

    @endphp


    <div class="sila-home overflow-hidden bg-[var(--livora-cream)]">


        {{-- =========================================================
             HERO
        ========================================================== --}}

        <section class="sila-home-hero relative">

            <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8">

                <div class="sila-home-hero-grid grid grid-cols-1 gap-6 py-4 sm:gap-7 lg:grid-cols-[0.92fr_1.08fr] lg:py-5">


                    {{-- =================================================
                         HERO CONTENT
                    ================================================== --}}

                    <div class="sila-home-hero-content relative flex flex-col justify-center rounded-[2rem] bg-[var(--livora-surface)] px-6 py-10 sm:px-9 sm:py-11 lg:px-12 lg:py-12">

                        <div class="max-w-xl">

                            <div class="flex items-center gap-3 text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">

                                <span class="h-px w-8 bg-[var(--livora-accent)]"></span>

                                KITCHEN APPLIANCES

                            </div>


                            <h1 class="sila-home-hero-title mt-6 font-semibold leading-[1.06] tracking-tight text-[var(--livora-ink)]">

                                آشپزخانه‌ای که

                                <span class="block text-[var(--livora-accent)]">
                                    با انتخاب درست، کامل می‌شود.
                                </span>

                            </h1>


                            <p class="mt-7 max-w-lg text-sm leading-8 text-[var(--livora-stone)] sm:text-base">

                                هود و سینک آشپزخانه را بر اساس فضای نصب، نیاز روزمره و مشخصات محصول انتخاب کنید؛
                                فرزین کمک می‌کند با دید روشن‌تری تصمیم بگیرید.

                            </p>


                            <div class="mt-9 flex flex-col gap-3 sm:flex-row">

                                {{-- CTA 01 --}}

                                <a
                                    href="{{ route('shop.index') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-4 text-sm font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:border-[var(--livora-ink)]"
                              >
                                    کشف مجموعه


                                </a>


                                {{-- CTA 02 --}}

                                <a
                                    href="{{ route('categories.index') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-4 text-sm font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:border-[var(--livora-ink)]"
                                >
                                    مشاهده دسته‌بندی‌ها
                                </a>

                            </div>

                        </div>


                        {{-- Hero Stats --}}

                        <div class="mt-12 grid max-w-xl grid-cols-3 gap-3">

                            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    PRODUCT DETAILS
                                </p>

                                <p class="mt-2 text-sm font-semibold">
                                    مشخصات روشن
                                </p>

                            </div>


                            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    SMART CHOICE
                                </p>

                                <p class="mt-2 text-sm font-semibold">
                                    انتخاب کاربردی
                                </p>

                            </div>


                            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    SUPPORT
                                </p>

                                <p class="mt-2 text-sm font-semibold">
                                    پاسخ‌گوی شما
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         HERO PRODUCT SLIDER
                    ================================================== --}}

                    <div
                        id="livoraHeroSlider"
                        class="sila-home-hero-slider relative min-h-[430px] overflow-hidden rounded-[2rem] bg-[var(--livora-white)] lg:min-h-0"
                    >

                        @if($heroProducts->isNotEmpty())

                            <div class="absolute inset-0">

                                @foreach($heroProducts as $index => $product)

                                    @php
                                        $image = $product->images?->first()?->url;
                                    @endphp

                                    <div
                                        class="livora-hero-slide absolute inset-0 transition-all duration-1000 ease-[cubic-bezier(0.22,1,0.36,1)] {{ $index === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 z-0' }}"
                                        data-slide="{{ $index }}"
                                    >

                                        <img
                                            src="{{ $image }}"
                                            alt="{{ $product->name }}"
                                            class="h-full w-full object-cover"
                                        >


                                        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/10 to-transparent"></div>

                                        <div class="absolute inset-0 bg-gradient-to-r from-black/10 via-transparent to-black/10"></div>


                                        {{-- Product info --}}

                                        <div class="absolute bottom-5 left-5 right-5 sm:bottom-7 sm:left-7 sm:right-7">

                                            <div
                                                class="livora-hero-info max-w-md translate-y-3 rounded-3xl border border-white/20 bg-black/20 p-5 text-white opacity-0 backdrop-blur-xl transition-all duration-1000 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                            >

                                                <div class="flex items-center justify-between gap-4">

                                                    <div class="min-w-0">

                                                        <p class="text-[10px] uppercase tracking-[0.18em] text-white/55">
                                                            Featured Product
                                                        </p>

                                                        <h2 class="mt-2 truncate text-lg font-semibold sm:text-xl">
                                                            {{ $product->name }}
                                                        </h2>

                                                        <p class="mt-2 text-sm text-white/75">
                                                            {{ number_format((float) $product->price) }}
                                                            تومان
                                                        </p>

                                                    </div>


                                                    {{-- Product CTA --}}

                                                    <a
                                                        href="{{ route('product.show', $product->slug) }}"
                                                        class="inline-flex shrink-0 items-center rounded-full bg-white px-4 py-2 text-xs font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:bg-[var(--livora-cream)]"
                                                    >
                                                        مشاهده
                                                    </a>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            </div>


                            {{-- Counter --}}

                            <div class="absolute right-5 top-5 z-30 sm:right-7 sm:top-7">

                                <div
                                    class="rounded-full border border-white/20 bg-black/20 px-4 py-2 text-[10px] font-medium tracking-[0.2em] text-white backdrop-blur-md"
                                >

                                    <span id="livoraHeroCurrent">
                                        01
                                    </span>

                                    <span class="mx-1 text-white/40">
                                        /
                                    </span>

                                    <span class="text-white/50">
                                        {{ str_pad($heroProducts->count(), 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                </div>

                            </div>


                            {{-- Progress --}}

                            <div class="absolute bottom-0 left-0 right-0 z-30 h-[2px] bg-white/15">

                                <div
                                    id="livoraHeroProgress"
                                    class="h-full origin-right bg-white"
                                    style="transform: scaleX(1);"
                                ></div>

                            </div>


                            {{-- Controls --}}

                            @if($heroProducts->count() > 1)

                                <div class="absolute left-5 top-5 z-30 flex gap-2 sm:left-7 sm:top-7">

                                    <button
                                        type="button"
                                        id="livoraHeroPrev"
                                        class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/20 text-white backdrop-blur-md transition-colors duration-300 hover:bg-white hover:text-black"
                                        aria-label="محصول قبلی"
                                    >
                                        →
                                    </button>


                                    <button
                                        type="button"
                                        id="livoraHeroNext"
                                        class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/20 text-white backdrop-blur-md transition-colors duration-300 hover:bg-white hover:text-black"
                                        aria-label="محصول بعدی"
                                    >
                                        ←
                                    </button>

                                </div>

                            @endif


                        @else

                            <div class="flex min-h-[430px] items-center justify-center bg-[var(--livora-surface)]">

                                <span class="text-sm tracking-[0.2em] text-[var(--livora-stone)]">
                                    فرزین
                                </span>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </section>


        {{-- =========================================================
             CATEGORY DISCOVERY
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-16 sm:py-20">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                        <div class="max-w-2xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                SHOP BY CATEGORY
                            </p>

                            <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                برای هر آشپزخانه، انتخابی دقیق.
                            </h2>

                            <p class="mt-4 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">

                                از هودهای آشپزخانه تا سینک‌های ظرفشویی، مدل‌ها را بر اساس نیاز و فضای نصب بررسی کنید.

                            </p>

                        </div>


                        <a
                            href="{{ route('categories.index') }}"
                            class="inline-flex items-center text-sm font-medium text-[var(--livora-accent)] transition-colors duration-300 hover:text-[var(--livora-ink)]"
                        >
                            همه دسته‌بندی‌ها

                            <span class="mr-2">
                                ←
                            </span>

                        </a>

                    </div>


                    <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-2">

                        @foreach($categories->take(4) as $category)

                            <x-shop.category-card
                                :name="$category->name"
                                :image="$category->homepage_image_url"
                                :href="route('categories.show', $category->slug)"
                                :count="$category->products_count"
                            />

                        @endforeach

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FEATURED COLLECTION
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)]">

            <x-layout.container>

                <div class="py-16 sm:py-20">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                Curated collection
                            </p>

                            <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                محصولات فرزین
                            </h2>

                            <p class="mt-3 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">
                                مشخصات، ویژگی‌ها و مدل‌های موجود را کنار هم ببینید و گزینه مناسب آشپزخانه خود را پیدا کنید.
                            </p>

                        </div>


                        <a
                            href="{{ route('shop.index') }}"
                            class="inline-flex items-center text-sm font-medium text-[var(--livora-accent)] transition-colors duration-300 hover:text-[var(--livora-ink)]"
                        >
                            مشاهده همه محصولات

                            <span class="mr-2">
                                ←
                            </span>

                        </a>

                    </div>


                    @if($featuredWithoutHero->isNotEmpty())

                        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-8 lg:grid-cols-4">

                            @foreach($featuredWithoutHero as $product)

                                <x-product.card
                                    :product="$product"
                                />

                            @endforeach

                        </div>

                    @else

                        <div class="mt-10 rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-10 text-center">

                            <p class="text-sm text-[var(--livora-stone)]">
                                محصولات فرزین پس از ثبت در پنل مدیریت، در این بخش نمایش داده می‌شوند.
                            </p>

                        </div>

                    @endif

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FARZIN CHOOSING GUIDE
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-ink)] text-white">
            <x-layout.container>
                <div class="py-16 sm:py-20">
                    <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                        <div>
                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-cyan-300">
                                FARZIN BUYING GUIDE
                            </p>
                            <h2 class="mt-4 max-w-xl text-3xl font-semibold tracking-tight sm:text-4xl lg:text-5xl">
                                انتخاب بهتر،
                                <span class="block text-cyan-300">از شناخت جزئیات شروع می‌شود.</span>
                            </h2>
                            <p class="mt-6 max-w-xl text-sm leading-8 text-white/70">
                                پیش از خرید هود یا سینک، ابعاد محل نصب، نوع استفاده و مشخصات فنی را بررسی کنید.
                                اگر بین چند مدل مردد هستید، برای انتخاب مناسب‌تر با فرزین تماس بگیرید.
                            </p>
                            <a href="{{ route('categories.index') }}"
                               class="mt-8 inline-flex items-center rounded-2xl bg-cyan-300 px-6 py-4 text-sm font-semibold text-slate-950 transition hover:bg-white">
                                بررسی دسته‌بندی‌ها
                            </a>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 sm:p-6">
                                <span class="text-[10px] uppercase tracking-[0.18em] text-white/50">01 / FIT</span>
                                <p class="mt-4 text-lg font-semibold">ابعاد و نصب</p>
                                <p class="mt-2 text-xs leading-6 text-white/60">عرض کابینت و فضای نصب را با اندازه‌های محصول تطبیق دهید.</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 sm:p-6">
                                <span class="text-[10px] uppercase tracking-[0.18em] text-white/50">02 / USE</span>
                                <p class="mt-4 text-lg font-semibold">نیاز روزمره</p>
                                <p class="mt-2 text-xs leading-6 text-white/60">امکانات و روش نگهداری را متناسب با سبک آشپزی انتخاب کنید.</p>
                            </div>
                            <div class="col-span-2 rounded-3xl border border-cyan-300/30 bg-cyan-300/10 p-5 sm:p-6">
                                <p class="text-sm font-semibold">اطلاعات شفاف، انتخاب مطمئن‌تر</p>
                                <p class="mt-2 text-xs leading-6 text-white/65">مشخصات و قیمت نهایی هر محصول باید در صفحه همان محصول بررسی شود.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-layout.container>
        </section>

        {{-- =========================================================
             NEW ARRIVALS
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)]">

            <x-layout.container>

                <div class="py-16 sm:py-20">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                New arrivals
                            </p>

                            <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                تازه‌های فرزین
                            </h2>

                            <p class="mt-3 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">
                                مدل‌هایی که به‌تازگی به کاتالوگ فرزین اضافه شده‌اند.
                            </p>

                        </div>


                        <a
                            href="{{ route('shop.index', ['sort' => 'newest']) }}"
                            class="text-sm font-medium text-[var(--livora-accent)] transition-colors duration-300 hover:text-[var(--livora-ink)]"
                        >
                            محصولات تازه
                        </a>

                    </div>


                    @if($newProducts->isNotEmpty())

                        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-8 lg:grid-cols-4">

                            @foreach($newProducts as $product)

                                <x-product.card
                                    :product="$product"
                                />

                            @endforeach

                        </div>

                    @else

                        <div class="mt-10 rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-10 text-center">

                            <p class="text-sm text-[var(--livora-stone)]">
                                به‌محض ثبت محصولات جدید در پنل مدیریت، اینجا نمایش داده می‌شوند.
                            </p>

                        </div>

                    @endif

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             INSPIRATION
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="py-16 sm:py-20">

                    <div class="grid items-end gap-8 lg:grid-cols-[0.85fr_1.15fr]">


                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                Inspiration
                            </p>

                            <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">

                                فقط یک انتخاب نیست.

                                <span class="block text-[var(--livora-accent)]">
                                    تفاوتی در کارایی روزمره است.
                                </span>

                            </h2>


                            <p class="mt-5 max-w-lg text-sm leading-8 text-[var(--livora-stone)]">

                                برای انتخاب هود و سینک، ابعاد کابینت، فضای نصب، جنس بدنه و شیوه نظافت را در نظر بگیرید.
                                راهنمای فرزین کمک می‌کند گزینه‌ها را آگاهانه‌تر مقایسه کنید.

                            </p>


                            <a
                                href="{{ route('about') }}"
                                class="mt-7 inline-flex items-center text-sm font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:text-[var(--livora-accent)]"
                            >
                                درباره فرزین

                                <span class="mr-2">
                                    ←
                                </span>

                            </a>

                        </div>


                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                            <article class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <span class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    01
                                </span>

                                <h3 class="mt-8 text-base font-semibold">
                                    ابعاد و فضای نصب
                                </h3>

                                <p class="mt-3 text-xs leading-7 text-[var(--livora-stone)]">
                                    پیش از سفارش، عرض کابینت و ابعاد محل نصب را با مشخصات محصول تطبیق دهید.
                                </p>

                            </article>


                            <article class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <span class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    02
                                </span>

                                <h3 class="mt-8 text-base font-semibold">
                                    جنس و نگهداری
                                </h3>

                                <p class="mt-3 text-xs leading-7 text-[var(--livora-stone)]">
                                    جنس سینک، نوع سطح و روش نظافت را با میزان استفاده روزانه هماهنگ کنید.
                                </p>

                            </article>


                            <article class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <span class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    03
                                </span>

                                <h3 class="mt-8 text-base font-semibold">
                                    مقایسه مشخصات
                                </h3>

                                <p class="mt-3 text-xs leading-7 text-[var(--livora-stone)]">
                                    ابعاد، نوع نصب، امکانات، گارانتی و قیمت را کنار هم مقایسه کنید.
                                </p>

                            </article>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             TRUST
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="grid grid-cols-1 divide-y divide-[var(--livora-border)] py-4 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4">

                    <div class="px-2 py-8 sm:px-8">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                            Selection
                        </p>

                        <h3 class="mt-3 text-sm font-semibold">
                            انتخاب با دقت
                        </h3>

                        <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                            محصولات با تمرکز بر فرم، کاربرد و کیفیت انتخاب می‌شوند.
                        </p>

                    </div>


                    <div class="px-2 py-8 sm:px-8">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                            SMART CHOICE
                        </p>

                        <h3 class="mt-3 text-sm font-semibold">
                            انتخاب متناسب
                        </h3>

                        <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                            برای برخی محصولات، شرایط خرید انتخاب کاربردی در دسترس است.
                        </p>

                    </div>


                    <div class="px-2 py-8 sm:px-8">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                            Support
                        </p>

                        <h3 class="mt-3 text-sm font-semibold">
                            همراهی تا خرید
                        </h3>

                        <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                            اطلاعات محصول و مسیر خرید را شفاف و ساده نگه می‌داریم.
                        </p>

                    </div>


                    <div class="px-2 py-8 sm:px-8">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                            Experience
                        </p>

                        <h3 class="mt-3 text-sm font-semibold">
                            تجربه‌ای آرام
                        </h3>

                        <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                            از کشف محصول تا پرداخت، همه‌چیز با کمترین اصطکاک طراحی شده است.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FINAL CTA
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)]">

            <x-layout.container>

                <div class="py-20 sm:py-28">

                    <div class="mx-auto max-w-3xl text-center">

                        <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                            فرزین
                        </p>

                        <h2 class="mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">
                            انتخابی دقیق برای آشپزخانه‌تان پیدا کنید.
                        </h2>

                        <p class="mx-auto mt-5 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                            مجموعه را ببینید، فضای خودتان را تصور کنید
                            و انتخابی انجام دهید که سال‌ها با شما بماند.
                        </p>

                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">

                            {{-- Final CTA 01 --}}

                            <a
                                href="{{ route('shop.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition-colors duration-300 hover:bg-[var(--livora-accent)]"
                            >
                                ورود به فروشگاه
                            </a>


                            {{-- Final CTA 02 --}}

                            <a
                                href="{{ route('contact') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-7 py-4 text-sm font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:border-[var(--livora-ink)]"
                            >
                                تماس با ما
                            </a>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection


@push('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const slider = document.getElementById('livoraHeroSlider');

            if (!slider) {
                return;
            }


            const slides = Array.from(
                slider.querySelectorAll('.livora-hero-slide')
            );


            if (slides.length <= 1) {
                return;
            }


            const currentEl = document.getElementById(
                'livoraHeroCurrent'
            );

            const progressEl = document.getElementById(
                'livoraHeroProgress'
            );

            const prevButton = document.getElementById(
                'livoraHeroPrev'
            );

            const nextButton = document.getElementById(
                'livoraHeroNext'
            );


            /*
            |--------------------------------------------------------------------------
            | Slider timing
            |--------------------------------------------------------------------------
            */

            const interval = 7000;


            let current = 0;
            let timer = null;


            /*
            |--------------------------------------------------------------------------
            | Counter
            |--------------------------------------------------------------------------
            */

            const updateCounter = () => {

                if (!currentEl) {
                    return;
                }

                currentEl.textContent = String(
                    current + 1
                ).padStart(2, '0');
            };


            /*
            |--------------------------------------------------------------------------
            | Product info animation
            |--------------------------------------------------------------------------
            */

            const animateInfo = (slide) => {

                slides.forEach((item) => {

                    const info = item.querySelector(
                        '.livora-hero-info'
                    );

                    if (!info) {
                        return;
                    }

                    info.classList.remove(
                        'translate-y-0',
                        'opacity-100'
                    );

                    info.classList.add(
                        'translate-y-3',
                        'opacity-0'
                    );

                });


                const activeInfo = slide.querySelector(
                    '.livora-hero-info'
                );


                if (!activeInfo) {
                    return;
                }


                setTimeout(() => {

                    activeInfo.classList.remove(
                        'translate-y-3',
                        'opacity-0'
                    );

                    activeInfo.classList.add(
                        'translate-y-0',
                        'opacity-100'
                    );

                }, 180);
            };


            /*
            |--------------------------------------------------------------------------
            | Progress
            |--------------------------------------------------------------------------
            */

            const resetProgress = () => {

                if (!progressEl) {
                    return;
                }


                progressEl.style.transition = 'none';

                progressEl.style.transform = 'scaleX(0)';


                requestAnimationFrame(() => {

                    requestAnimationFrame(() => {

                        progressEl.style.transition =
                            `transform ${interval}ms linear`;

                        progressEl.style.transform =
                            'scaleX(1)';

                    });

                });

            };


            /*
            |--------------------------------------------------------------------------
            | Show slide
            |--------------------------------------------------------------------------
            */

            const showSlide = (index) => {

                slides.forEach(
                    (slide, slideIndex) => {

                        if (slideIndex === index) {

                            slide.classList.remove(
                                'opacity-0',
                                'scale-105',
                                'z-0'
                            );

                            slide.classList.add(
                                'opacity-100',
                                'scale-100',
                                'z-10'
                            );

                        } else {

                            slide.classList.remove(
                                'opacity-100',
                                'scale-100',
                                'z-10'
                            );

                            slide.classList.add(
                                'opacity-0',
                                'scale-105',
                                'z-0'
                            );

                        }

                    }
                );


                current = index;

                updateCounter();

                animateInfo(slides[current]);

                resetProgress();
            };


            /*
            |--------------------------------------------------------------------------
            | Next
            |--------------------------------------------------------------------------
            */

            const nextSlide = () => {

                const next =
                    (current + 1) % slides.length;

                showSlide(next);
            };


            /*
            |--------------------------------------------------------------------------
            | Previous
            |--------------------------------------------------------------------------
            */

            const prevSlide = () => {

                const previous =
                    (current - 1 + slides.length) % slides.length;

                showSlide(previous);
            };


            /*
            |--------------------------------------------------------------------------
            | Autoplay
            |--------------------------------------------------------------------------
            */

            const stopAutoPlay = () => {

                if (!timer) {
                    return;
                }

                clearInterval(timer);

                timer = null;
            };


            const startAutoPlay = () => {

                stopAutoPlay();

                timer = setInterval(() => {

                    nextSlide();

                }, interval);
            };


            /*
            |--------------------------------------------------------------------------
            | Controls
            |--------------------------------------------------------------------------
            */

            if (nextButton) {

                nextButton.addEventListener('click', () => {

                    nextSlide();

                    startAutoPlay();

                });
            }


            if (prevButton) {

                prevButton.addEventListener('click', () => {

                    prevSlide();

                    startAutoPlay();

                });
            }


            /*
            |--------------------------------------------------------------------------
            | Initial state
            |--------------------------------------------------------------------------
            */

            updateCounter();

            animateInfo(slides[0]);

            resetProgress();

            startAutoPlay();

        });
    </script>

@endpush
