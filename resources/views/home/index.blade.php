@extends('layouts.app')

@section('title', 'فرجی‌هوم | خرید هود، اجاق گاز و سینک آشپزخانه')
@section('description', 'هود، اجاق گاز صفحه‌ای و سینک را در فرجی‌هوم با تصویر، قیمت و مشخصات فنی بررسی و مقایسه کنید.')
@section('canonical', route('home'))
@section('body_class', 'home-body min-h-screen antialiased')

@push('styles')
    @vite('resources/css/home.css')
@endpush

@section('content')
@php
    $homeCopy = $storefrontPageContent ?? [];
    $showCategories = ($homeCopy['page_home_show_categories'] ?? '1') === '1';
    $showProducts = ($homeCopy['page_home_show_products'] ?? '1') === '1';
    $showBenefits = ($homeCopy['page_home_show_trust'] ?? '1') === '1';
    $showGuides = ($homeCopy['page_home_show_guides'] ?? '1') === '1';
    $showJournal = ($homeCopy['page_home_show_journal'] ?? '1') === '1';
    $categoriesEyebrow = trim((string) ($homeCopy['page_home_categories_eyebrow'] ?? ''));
    $categoriesTitle = trim((string) ($homeCopy['page_home_categories_title'] ?? 'دسته‌بندی محصولات'));
    $featuredProducts = $featuredProducts ?? collect();
    $productShelfTitle = trim((string) ($homeCopy['page_home_products_title'] ?? 'محصولات منتخب')) ?: 'منتخب فرجی‌هوم';
    $productShelfEyebrow = trim((string) ($homeCopy['page_home_products_eyebrow'] ?? 'محصولات آشپزخانه'));
    $hoodCategory = $categories->first(fn ($category) => $category->slug === 'hoods' || \Illuminate\Support\Str::contains(mb_strtolower($category->name), 'هود'));
    $sinkCategory = $categories->first(fn ($category) => $category->slug === 'sinks' || \Illuminate\Support\Str::contains(mb_strtolower($category->name), 'سینک'));
    $gasCategory = $categories->first(fn ($category) => in_array($category->slug, ['built-in-stove', 'gas-hobs', 'built-in-gas', 'built-in-hobs', 'gas-stove', 'gas-stoves', 'stoves'], true)
        || \Illuminate\Support\Str::contains(mb_strtolower($category->name), ['اجاق', 'گاز']));
    $selectionGuides = collect([
        [
            'number' => '۰۱',
            'eyebrow' => 'تهویه و گردش هوا',
            'title' => $homeCopy['page_home_hood_guide_title'] ?? 'راهنمای انتخاب هود',
            'description' => $homeCopy['page_home_hood_guide_description'] ?? 'ابعاد، نوع نصب، مکش و صدای هود را مقایسه کنید.',
            'cta' => $homeCopy['page_home_hood_guide_cta'] ?? 'مشاهده هودها',
            'href' => $hoodCategory ? route('categories.show', $hoodCategory->slug) : route('shop.index', ['search' => 'هود']),
            'icon' => 'hood',
        ],
        [
            'number' => '۰۲',
            'eyebrow' => 'پخت‌وپز روزمره',
            'title' => $homeCopy['page_home_gas_guide_title'] ?? 'راهنمای انتخاب اجاق گاز صفحه‌ای',
            'description' => $homeCopy['page_home_gas_guide_description'] ?? 'ابعاد برش، تعداد شعله و امکانات ایمنی را بررسی کنید.',
            'cta' => $homeCopy['page_home_gas_guide_cta'] ?? 'مشاهده اجاق گازها',
            'href' => $gasCategory ? route('categories.show', $gasCategory->slug) : route('shop.index', ['search' => 'اجاق گاز']),
            'icon' => 'gas',
        ],
        [
            'number' => '۰۳',
            'eyebrow' => 'آماده‌سازی و شست‌وشو',
            'title' => $homeCopy['page_home_sink_guide_title'] ?? 'راهنمای انتخاب سینک',
            'description' => $homeCopy['page_home_sink_guide_description'] ?? 'روش نصب، ابعاد، عمق و تعداد لگن را تطبیق دهید.',
            'cta' => $homeCopy['page_home_sink_guide_cta'] ?? 'مشاهده سینک‌ها',
            'href' => $sinkCategory ? route('categories.show', $sinkCategory->slug) : route('shop.index', ['search' => 'سینک']),
            'icon' => 'sink',
        ],
    ]);
    $enamadUrl = trim((string) ($enamadUrl ?? ''));
    if (!\Illuminate\Support\Str::startsWith($enamadUrl, 'https://')) {
        $enamadUrl = '';
    }
@endphp

<div class="fz-home-market" data-fz-home>
    @if($bannerSlides->isNotEmpty())
        <section class="fz-market-hero" aria-label="بنرهای اصلی فرجی‌هوم" data-fz-hero-slider>
            <div class="fz-market-hero__container">
                <div class="fz-market-hero__slides">
                    @foreach($bannerSlides as $index => $slide)
                        <article
                            class="fz-market-hero__slide {{ $index === 0 ? 'is-active' : '' }}"
                            data-fz-hero-slide
                            aria-roledescription="اسلاید"
                            aria-label="اسلاید {{ \App\Helpers\PersianNumberHelper::format($index + 1) }} از {{ \App\Helpers\PersianNumberHelper::format($bannerSlides->count()) }}"
                            @if($index !== 0) hidden @endif
                        >
                            @if($slide['image'])
                                <img
                                    class="fz-market-hero__image"
                                    src="{{ $slide['image'] }}"
                                    alt="{{ $slide['alt'] }}"
                                    width="1920"
                                    height="720"
                                    @if($index === 0) fetchpriority="high" loading="eager" @else loading="lazy" @endif
                                    decoding="async"
                                >
                            @endif
                            <div class="fz-market-hero__shade" aria-hidden="true"></div>
                            <div class="fz-market-hero__copy">
                                @if($slide['eyebrow'] !== '')
                                    <p class="fz-market-hero__eyebrow">{{ $slide['eyebrow'] }}</p>
                                @endif
                                @if($index === 0)
                                    <h1>{{ $homeCopy['page_home_hero_title'] ?? $slide['title'] }}</h1>
                                @else
                                    <h2>{{ $slide['title'] }}</h2>
                                @endif
                                @php($heroDescription = $index === 0 ? ($homeCopy['page_home_hero_description'] ?? $slide['description']) : $slide['description'])
                                @if(trim((string) $heroDescription) !== '')
                                    <p class="fz-market-hero__description">{{ $heroDescription }}</p>
                                @endif
                                @if($slide['button_label'] !== '')
                                    <a class="fz-market-hero__button" href="{{ $slide['button_url'] }}">
                                        {{ $slide['button_label'] }}
                                        <span aria-hidden="true">←</span>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($bannerSlides->count() > 1)
                    <div class="fz-market-hero__controls">
                        <button type="button" class="fz-market-hero__arrow" data-fz-hero-prev aria-label="بنر قبلی">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
                        </button>
                        <div class="fz-market-hero__dots" role="group" aria-label="انتخاب بنر">
                            @foreach($bannerSlides as $index => $slide)
                                <button
                                    type="button"
                                    data-fz-hero-dot="{{ $index }}"
                                    aria-label="نمایش بنر {{ \App\Helpers\PersianNumberHelper::format($index + 1) }}"
                                    @if($index === 0) aria-current="true" @endif
                                ></button>
                            @endforeach
                        </div>
                        <button type="button" class="fz-market-hero__arrow" data-fz-hero-next aria-label="بنر بعدی">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if($showCategories && $categories->isNotEmpty())
        <section class="fz-home-categories" aria-labelledby="fz-home-categories-title">
            <div class="fz-home-container">
                <header class="fz-home-section-head">
                    <div>
                        @if($categoriesEyebrow !== '')
                            <span class="fz-home-section-head__eyebrow">{{ $categoriesEyebrow }}</span>
                        @endif
                        @if($categoriesTitle !== '')
                            <h2 id="fz-home-categories-title">{{ $categoriesTitle }}</h2>
                        @endif
                        @if(filled($homeCopy['page_home_categories_description'] ?? null))
                            <p>{{ $homeCopy['page_home_categories_description'] }}</p>
                        @endif
                    </div>
                    <a href="{{ route('categories.index') }}" class="fz-home-section-head__link">همه دسته‌ها <span aria-hidden="true">←</span></a>
                </header>
                <div class="fz-home-category-rail">
                    @foreach($categories as $category)
                        <x-home.round-category-card :category="$category" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($showGuides)
        <section class="border-y border-[var(--livora-border)] bg-[var(--livora-surface)]" aria-labelledby="fz-home-guides-title">
            <div class="fz-home-container py-12 sm:py-16">
                <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div class="max-w-2xl">
                        <span class="text-xs font-bold text-[var(--livora-accent)]">فرجی‌هوم / راهنمای خرید</span>
                        <h2 id="fz-home-guides-title" class="mt-3 text-2xl font-black tracking-tight text-[var(--livora-ink)] sm:text-3xl">
                            {{ $homeCopy['page_home_guides_title'] ?? 'از نیاز آشپزخانه‌تان شروع کنید.' }}
                        </h2>
                        <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                            {{ $homeCopy['page_home_guides_description'] ?? 'دسته مناسب را انتخاب کنید و معیارهای مهم هر محصول را پیش از خرید بشناسید.' }}
                        </p>
                    </div>
                    <a href="{{ route('categories.index') }}" class="inline-flex min-h-11 w-fit items-center gap-2 text-sm font-bold text-[var(--livora-accent)] transition hover:gap-3">
                        همه دسته‌بندی‌ها <span aria-hidden="true">←</span>
                    </a>
                </header>
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach($selectionGuides as $guide)
                        <a href="{{ $guide['href'] }}" class="group flex min-h-56 flex-col border border-[var(--livora-border)] bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-accent)] hover:shadow-lg sm:p-7">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-xs font-bold tracking-widest text-[var(--livora-accent)]">{{ $guide['number'] }}</span>
                                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--livora-cream)] text-[var(--livora-ink)] transition group-hover:bg-[var(--livora-accent)] group-hover:text-white" aria-hidden="true">
                                    @if($guide['icon'] === 'hood')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M8 3h8v5l3 3v4H5v-4l3-3V3Z"/><path d="M7 16v4h10v-4M10 5h4M10 8h4"/></svg>
                                    @elseif($guide['icon'] === 'gas')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8" cy="10" r="2"/><circle cx="16" cy="10" r="2"/><circle cx="8" cy="15" r="1"/><circle cx="16" cy="15" r="1"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="M6 8h12v8H6zM12 5v3"/></svg>
                                    @endif
                                </span>
                            </div>
                            <span class="mt-6 text-[11px] font-semibold text-[var(--livora-stone)]">{{ $guide['eyebrow'] }}</span>
                            <h3 class="mt-2 text-xl font-black leading-8 text-[var(--livora-ink)]">{{ $guide['title'] }}</h3>
                            <p class="mt-2 flex-1 text-sm leading-7 text-[var(--livora-stone)]">{{ $guide['description'] }}</p>
                            <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[var(--livora-accent)]">
                                {{ $guide['cta'] }} <span aria-hidden="true" class="transition group-hover:-translate-x-1">←</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($showBenefits && $benefits->isNotEmpty())
        <section class="fz-home-benefits" aria-label="خدمات و مزیت‌های ثبت‌شده فرجی‌هوم">
            <div class="fz-home-container">
                <div class="fz-home-benefits__grid">
                    @foreach($benefits as $benefit)
                        <article class="fz-home-benefit">
                            <span class="fz-home-benefit__mark" aria-hidden="true">
                                @switch($benefit['key'])
                                    @case('shipping')
                                        <svg viewBox="0 0 24 24"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7zM7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg>
                                        @break
                                    @case('installation')
                                        <svg viewBox="0 0 24 24"><path d="m14.5 6.5 3 3M4 20l8.5-8.5m2-8A5 5 0 0 0 21 10l-4 4-7-7 4-4A5 5 0 0 0 7 9l-3 3 8 8 3-3"/></svg>
                                        @break
                                    @case('warranty')
                                        <svg viewBox="0 0 24 24"><path d="m12 3 7 3v5c0 4.5-2.9 7.8-7 10-4.1-2.2-7-5.5-7-10V6l7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                                        @break
                                    @case('installment')
                                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                                        @break
                                    @case('returns')
                                        <svg viewBox="0 0 24 24"><path d="M9 14 4 9l5-5"/><path d="M4 9h9a7 7 0 0 1 0 14h-2"/><path d="M15 17h5v5"/></svg>
                                        @break
                                @endswitch
                            </span>
                            <div class="fz-home-benefit__copy">
                                <h3>{{ $benefit['title'] }}</h3>
                                <p>{{ $benefit['description'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($showProducts && $featuredProducts->isNotEmpty())
        <x-home.product-carousel-section
            id="fz-home-featured"
            :title="$productShelfTitle"
            :eyebrow="$productShelfEyebrow"
            :products="$featuredProducts"
        />
    @endif

    @if($showProducts && $discountProducts->isNotEmpty())
        <x-home.product-carousel-section
            id="fz-home-discounts"
            title="پیشنهادهای ویژه"
            eyebrow="فرصت‌های خرید"
            :products="$discountProducts"
        />
    @endif

    @if($showProducts && $bestSellingProducts->isNotEmpty())
        <x-home.product-carousel-section
            id="fz-home-best-sellers"
            title="پرفروش‌های فروشگاه"
            eyebrow="محبوب میان خریداران"
            :products="$bestSellingProducts"
        />
    @endif

    @if($showProducts && $newestProducts->isNotEmpty())
        <x-home.product-carousel-section
            id="fz-home-new-arrivals"
            title="تازه‌رسیده‌ها"
            eyebrow="جدیدترین مدل‌ها"
            :products="$newestProducts"
        />
    @endif

    @if(($homeTechnology['show'] ?? false) && (filled($homeTechnology['title'] ?? null) || filled($homeTechnology['body'] ?? null)))
        <section class="fz-home-technology" aria-labelledby="fz-home-technology-title">
            <div class="fz-home-container fz-home-technology__layout">
                <div class="fz-home-technology__copy">
                    @if(filled($homeTechnology['eyebrow'] ?? null))
                        <span class="fz-home-technology__eyebrow">{{ $homeTechnology['eyebrow'] }}</span>
                    @endif
                    <h2 id="fz-home-technology-title">{{ $homeTechnology['title'] }}</h2>
                    @if(filled($homeTechnology['body'] ?? null))
                        <p>{{ $homeTechnology['body'] }}</p>
                    @endif
                    <div class="fz-home-technology__signals" aria-label="معیارهای انتخاب محصول">
                        <span>طراحی و تناسب</span>
                        <span>جنس و پرداخت</span>
                        <span>کاربری و نصب</span>
                    </div>
                    <a href="{{ route('about') }}" class="fz-home-technology__link">
                        {{ $homeTechnology['button_label'] ?: 'آشنایی با فرجی‌هوم' }}
                        <span aria-hidden="true">←</span>
                    </a>
                </div>
                <div class="fz-home-technology__visual">
                    @if(filled($homeTechnology['image_url'] ?? null))
                        <img
                            src="{{ $homeTechnology['image_url'] }}"
                            @if(filled($homeTechnology['image_srcset'] ?? null)) srcset="{{ $homeTechnology['image_srcset'] }}" sizes="(max-width: 767px) 94vw, 48vw" @endif
                            alt="{{ $homeTechnology['title'] }}"
                            width="1200"
                            height="750"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="fz-home-technology__placeholder" aria-label="راهنمای انتخاب تجهیزات آشپزخانه">
                            <span>فرجی‌هوم / راهنمای انتخاب</span>
                            <strong>فرم، عملکرد، جزئیات</strong>
                            <small>معیارهای مهم برای مقایسه مدل‌ها؛ از ابعاد نصب تا امکانات و نگهداری.</small>
                        </div>
                    @endif
                    <span class="fz-home-technology__index" aria-hidden="true">01 / 03</span>
                </div>
            </div>
        </section>
    @endif

    @if($showJournal && $blogPosts->isNotEmpty())
        <section class="fz-home-journal" aria-labelledby="fz-home-journal-title">
            <div class="fz-home-container">
                <header class="fz-home-section-head">
                    <div>
                        <span class="fz-home-section-head__eyebrow">مجله و راهنمای خرید</span>
                        <h2 id="fz-home-journal-title">{{ $homeCopy['page_home_journal_title'] ?? 'مجله و راهنمای انتخاب' }}</h2>
                        @if(filled($homeCopy['page_home_journal_description'] ?? null))
                            <p>{{ $homeCopy['page_home_journal_description'] }}</p>
                        @endif
                    </div>
                    <a href="{{ route('blog.index') }}" class="fz-home-section-head__link">همه مقاله‌ها <span aria-hidden="true">←</span></a>
                </header>

                <div class="fz-home-journal__grid">
                    @foreach($blogPosts->take(3) as $article)
                        <x-blog.article-card :post="$article" variant="home" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if(($homeCopy['page_home_show_final_cta'] ?? '1') === '1')
        <section class="fz-home-final-cta" aria-labelledby="fz-home-final-cta-title">
            <div class="fz-home-container">
                <div class="fz-home-final-cta__inner">
                    <div>
                        <span class="fz-home-section-head__eyebrow">فرجی‌هوم / انتخاب آگاهانه</span>
                        <h2 id="fz-home-final-cta-title">{{ $homeCopy['page_home_final_title'] ?? 'مدل مناسب آشپزخانه‌تان را پیدا کنید.' }}</h2>
                        @if(filled($homeCopy['page_home_final_description'] ?? null))
                            <p>{{ $homeCopy['page_home_final_description'] }}</p>
                        @endif
                    </div>
                    <a href="{{ route('shop.index') }}" class="fz-home-final-cta__button">
                        {{ $homeCopy['page_home_final_cta'] ?? 'ورود به فروشگاه' }}
                        <span aria-hidden="true">←</span>
                    </a>
                </div>
            </div>
        </section>
    @endif

    @if($enamadUrl !== '')
        <div class="fz-home-enamad" aria-label="نماد اعتماد الکترونیکی">
            <div class="fz-home-container">
                <a href="{{ $enamadUrl }}" target="_blank" rel="noopener noreferrer">مشاهده نماد اعتماد الکترونیکی فرجی‌هوم <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    @endif
</div>
@endsection
