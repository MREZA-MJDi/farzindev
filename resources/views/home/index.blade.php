@extends('layouts.app')

@section('title', 'فرزین | خرید آنلاین هود و سینک آشپزخانه')
@section('description', 'محصولات واقعی هود و سینک فرزین را با تصویر، قیمت و مشخصات بررسی و آنلاین سفارش دهید.')
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
    $showJournal = ($homeCopy['page_home_show_journal'] ?? '1') === '1';
    $categoriesEyebrow = trim((string) ($homeCopy['page_home_categories_eyebrow'] ?? ''));
    $categoriesTitle = trim((string) ($homeCopy['page_home_categories_title'] ?? 'دسته‌بندی محصولات'));
    $featuredProducts = $featuredProducts ?? collect();
    $productShelfTitle = trim((string) ($homeCopy['page_home_products_title'] ?? 'منتخب فرزین')) ?: 'منتخب فرزین';
    $productShelfEyebrow = trim((string) ($homeCopy['page_home_products_eyebrow'] ?? 'کاتالوگ هود و سینک'));
    $enamadUrl = trim((string) ($enamadUrl ?? ''));
    if (!\Illuminate\Support\Str::startsWith($enamadUrl, 'https://')) {
        $enamadUrl = '';
    }
@endphp

<div class="fz-home-market" data-fz-home>
    @if($bannerSlides->isNotEmpty())
        <section class="fz-market-hero" aria-label="بنرهای اصلی فرزین" data-fz-hero-slider>
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
                                <h1>{{ $slide['title'] }}</h1>
                                @if($slide['description'] !== '')
                                    <p class="fz-market-hero__description">{{ $slide['description'] }}</p>
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

    @if($showBenefits && $benefits->isNotEmpty())
        <section class="fz-home-benefits" aria-label="خدمات و مزیت‌های ثبت‌شده فرزین">
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
            eyebrow="فرصت‌های خرید فرزین"
            :products="$discountProducts"
        />
    @endif

    @if($showProducts && $bestSellingProducts->isNotEmpty())
        <x-home.product-carousel-section
            id="fz-home-best-sellers"
            title="پرفروش‌های فرزین"
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
                    <div class="fz-home-technology__signals" aria-label="محورهای توجه فرزین">
                        <span>طراحی و تناسب</span>
                        <span>جنس و پرداخت</span>
                        <span>کاربری و نصب</span>
                    </div>
                    <a href="{{ route('about') }}" class="fz-home-technology__link">
                        {{ $homeTechnology['button_label'] ?: 'آشنایی با فرزین' }}
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
                        <div class="fz-home-technology__placeholder" aria-label="فضای تصویر فناوری فرزین">
                            <span>FARZIN / ENGINEERING</span>
                            <strong>فرم، عملکرد، جزئیات</strong>
                            <small>نگاهی نزدیک به جزئیات ساخت، انتخاب متریال و تجربه استفاده روزمره.</small>
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
                        <span class="fz-home-section-head__eyebrow">دانش‌نامه فرزین</span>
                        <h2 id="fz-home-journal-title">{{ $homeCopy['page_home_journal_title'] ?? 'مجله و راهنمای انتخاب' }}</h2>
                        @if(filled($homeCopy['page_home_journal_description'] ?? null))
                            <p>{{ $homeCopy['page_home_journal_description'] }}</p>
                        @endif
                    </div>
                    <a href="{{ route('blog.index') }}" class="fz-home-section-head__link">همه مقاله‌ها <span aria-hidden="true">←</span></a>
                </header>

                <div class="fz-home-journal__grid">
                    @foreach($blogPosts->take(3) as $article)
                        @php($articleCategory = $article->terms->firstWhere('type', 'category'))
                        <article class="fz-home-article-card">
                            <a class="fz-home-article-card__media" href="{{ route('blog.show', $article->slug) }}" aria-label="مطالعه {{ $article->title }}">
                                <img
                                    src="{{ $article->cover_image_url }}"
                                    @if($article->cover_image_srcset) srcset="{{ $article->cover_image_srcset }}" sizes="(max-width: 767px) 88vw, (max-width: 1023px) 45vw, 360px" @endif
                                    alt="{{ $article->cover_image_alt ?: $article->title }}"
                                    width="900"
                                    height="620"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </a>
                            <div class="fz-home-article-card__body">
                                <div class="fz-home-article-card__meta">
                                    @if($articleCategory)<span>{{ $articleCategory->name }}</span>@endif
                                    <span>{{ \App\Helpers\PersianNumberHelper::format($article->reading_time) }} دقیقه مطالعه</span>
                                </div>
                                <h3><a href="{{ route('blog.show', $article->slug) }}">{{ $article->title }}</a></h3>
                                @if(filled($article->excerpt))
                                    <p>{{ \Illuminate\Support\Str::limit($article->excerpt, 125) }}</p>
                                @endif
                                <a class="fz-home-article-card__link" href="{{ route('blog.show', $article->slug) }}">ادامه مطلب <span aria-hidden="true">←</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($enamadUrl !== '')
        <div class="fz-home-enamad" aria-label="نماد اعتماد الکترونیکی">
            <div class="fz-home-container">
                <a href="{{ $enamadUrl }}" target="_blank" rel="noopener noreferrer">مشاهده نماد اعتماد الکترونیکی فرزین <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    @endif
</div>
@endsection
