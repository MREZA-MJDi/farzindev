@extends('layouts.app')

@section('title', 'فرزین | خرید آنلاین هود و سینک آشپزخانه')
@section('description', 'هود و سینک فرزین را با عکس، قیمت و مشخصات واقعی بررسی کنید؛ مدل مناسب آشپزخانه‌تان را انتخاب و آنلاین سفارش دهید.')
@section('canonical', route('home'))
@section('body_class', 'home-body min-h-screen antialiased')

@push('styles')
    @vite('resources/css/home.css')
@endpush

@section('content')
@php
    $homeCopy = $storefrontPageContent ?? [];
    $homeSettings = $storefrontPublicSettings ?? [];

    $heroImageValue = trim((string) ($homeCopy['page_home_hero_image_url'] ?? ''));
    $heroImageUrl = '';
    if ($heroImageValue !== '') {
        $heroImageUrl = \Illuminate\Support\Str::startsWith($heroImageValue, ['http://', 'https://', '//'])
            ? $heroImageValue
            : \Illuminate\Support\Facades\Storage::disk('public')->url(
                preg_replace('#^(public/|storage/)#', '', ltrim($heroImageValue, '/')) ?? $heroImageValue
            );
    }

    $heroTitle = trim((string) ($homeCopy['page_home_hero_title'] ?? ''))
        ?: 'هود و سینک، آماده‌ی نصب در آشپزخانه‌ی شما';
    $heroDescription = trim((string) ($homeCopy['page_home_hero_description'] ?? ''))
        ?: 'مدل‌های موجود فرزین را با تصویر، قیمت و مشخصات واقعی بررسی کنید.';
    $heroButtonLabel = trim((string) ($homeCopy['page_home_hero_primary_label'] ?? ''))
        ?: 'مشاهده محصولات';

    $phone = \App\Support\StorefrontContact::phone($contactSetting?->phone);
    $whatsappDigits = \App\Support\StorefrontContact::whatsapp($contactSetting?->whatsapp);
    $address = trim((string) ($contactSetting?->address ?? ''));

    $warrantyText = trim((string) ($homeSettings['warranty_text'] ?? ''));
    if ($warrantyText === '') {
        $warrantyText = trim((string) ($homeCopy['page_home_warranty_text'] ?? ''));
    }

    $enamadUrl = trim((string) ($homeCopy['page_home_enamad_url'] ?? ''));
    if (! \Illuminate\Support\Str::startsWith($enamadUrl, 'https://')) {
        $enamadUrl = '';
    }

    $showCategories = ($homeCopy['page_home_show_categories'] ?? '1') === '1';
    $showProducts = ($homeCopy['page_home_show_products'] ?? '1') === '1';
    $showTrust = ($homeCopy['page_home_show_trust'] ?? '1') === '1';
    $showGuides = ($homeCopy['page_home_show_guides'] ?? '1') === '1';
    $showJournal = ($homeCopy['page_home_show_journal'] ?? '1') === '1';
    $showFinalCta = ($homeCopy['page_home_show_final_cta'] ?? '1') === '1';

    $installmentCashAmount = $installmentProduct
        ? round((float) $installmentProduct->price * ((int) $installmentProduct->installment_cash_percent / 100))
        : 0;

    $hoodCategory = $categories->firstWhere('slug', 'hoods');
    $sinkCategory = $categories->firstWhere('slug', 'sinks');

    $hasTrustDetails = filled($warrantyText)
        || (bool) $whatsappDigits
        || $enamadUrl !== ''
        || filled($address)
        || (bool) $phone;
@endphp

<section class="fz-sales-hero" aria-labelledby="fz-sales-hero-title">
    <div class="fz-container fz-sales-hero__layout">
        <div class="fz-sales-hero__copy">
            <p class="fz-sales-hero__eyebrow">تجهیزات آشپزخانه فرزین</p>
            <h1 id="fz-sales-hero-title">{{ $heroTitle }}</h1>
            <p class="fz-sales-hero__lead">{{ $heroDescription }}</p>
            <a class="fz-sales-button fz-sales-button--primary" href="{{ route('shop.index') }}">
                {{ $heroButtonLabel }}
                <span aria-hidden="true">←</span>
            </a>
        </div>

        <div class="fz-sales-hero__visual">
            @if($heroImageUrl !== '')
                <img
                    src="{{ $heroImageUrl }}"
                    alt="آشپزخانه واقعی با تجهیزات نصب‌شده فرزین"
                    width="1600"
                    height="1000"
                    fetchpriority="high"
                    loading="eager"
                    decoding="async"
                >
                <span class="fz-sales-hero__image-caption">هود و سینک فرزین در فضای آشپزخانه</span>
            @else
                <div class="fz-sales-hero__image-placeholder" role="img" aria-label="تصویر واقعی آشپزخانه هنوز در مدیریت محتوا بارگذاری نشده است">
                    <span class="fz-sales-hero__placeholder-mark" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none">
                            <rect x="5" y="8" width="38" height="32" rx="5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="m9 34 10-10 7 7 5-5 8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="32" cy="17" r="3" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </span>
                    <span class="fz-sales-hero__placeholder-title">تصویر واقعی آشپزخانه</span>
                    <span class="fz-sales-hero__placeholder-copy">عکس محیط نصب‌شده را از مدیریت محتوای صفحه اصلی بارگذاری کنید.</span>
                </div>
            @endif

            @if($heroProduct)
                <x-home.hero-offer :product="$heroProduct" />
            @endif
        </div>
    </div>
</section>

<section class="fz-home-benefits" aria-label="مزیت‌های خرید از فرزین">
    <div class="fz-container fz-home-benefits__grid">
        <article class="fz-home-benefit">
            <span class="fz-home-benefit__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="7" cy="18" r="2" stroke="currentColor" stroke-width="1.7"/><circle cx="18" cy="18" r="2" stroke="currentColor" stroke-width="1.7"/></svg>
            </span>
            <strong>ارسال سفارش</strong>
        </article>
        <article class="fz-home-benefit">
            <span class="fz-home-benefit__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 20h16M6 20V8l6-4 6 4v12M9 11h6M9 15h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <strong>راهنمای نصب</strong>
        </article>
        <article class="fz-home-benefit">
            <span class="fz-home-benefit__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="m12 3 7 3v5c0 4.5-2.9 7.8-7 10-4.1-2.2-7-5.5-7-10V6l7-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <strong>گارانتی محصول</strong>
        </article>
        <article class="fz-home-benefit">
            <span class="fz-home-benefit__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 6h16M6 3v6m12-6v6M5 12h14v8H5zM9 16h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <strong>خرید اقساطی</strong>
        </article>
    </div>
</section>

@if($showCategories && $categories->isNotEmpty())
    <section id="product-categories" class="fz-home-section fz-home-categories" aria-labelledby="fz-home-categories-title">
        <div class="fz-container">
            <header class="fz-home-section__heading">
                <div>
                    <p class="fz-home-eyebrow">{{ $homeCopy['page_home_categories_eyebrow'] ?? 'دسته‌بندی محصولات فرزین' }}</p>
                    <h2 id="fz-home-categories-title">{{ $homeCopy['page_home_categories_title'] ?? 'از دسته‌ی مناسب شروع کنید.' }}</h2>
                    <p>{{ $homeCopy['page_home_categories_description'] ?? 'دسته‌های فعال و محصولات واقعی فرزین را ببینید.' }}</p>
                </div>
                <a class="fz-home-text-link" href="{{ route('categories.index') }}">همه دسته‌بندی‌ها <span aria-hidden="true">←</span></a>
            </header>
            <div class="fz-home-category-grid">
                @foreach($categories as $index => $category)
                    <x-home.category-card :category="$category" :index="$index" />
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($showProducts)
    <section class="fz-home-section fz-home-products" aria-labelledby="fz-home-products-title">
        <div class="fz-container">
            <header class="fz-home-section__heading">
                <div>
                    <p class="fz-home-eyebrow">{{ $homeCopy['page_home_products_eyebrow'] ?? 'کاتالوگ محصولات فرزین' }}</p>
                    <h2 id="fz-home-products-title">{{ $homeCopy['page_home_products_title'] ?? 'مدل‌های منتخب فرزین' }}</h2>
                    <p>{{ $homeCopy['page_home_products_description'] ?? 'موجودها در ابتدا و مدل‌های ناموجود در انتهای فهرست قرار می‌گیرند.' }}</p>
                </div>
                <a class="fz-home-text-link" href="{{ route('shop.index') }}">همه محصولات <span aria-hidden="true">←</span></a>
            </header>

            @if($featuredProducts->isNotEmpty())
                <div class="fz-home-product-grid">
                    @foreach($featuredProducts as $product)
                        <x-home.featured-product-card :product="$product" />
                    @endforeach
                </div>
            @else
                <div class="fz-home-empty">
                    <h3>هنوز محصولی برای نمایش در این بخش انتخاب نشده است.</h3>
                    <p>محصول‌های واقعی را از مدیریت محصولات برای نمایش در صفحه اصلی انتخاب کنید.</p>
                    <a class="fz-sales-button fz-sales-button--primary" href="{{ route('shop.index') }}">رفتن به فروشگاه</a>
                </div>
            @endif
        </div>
    </section>
@endif

@if($installmentProduct)
    <section class="fz-home-section fz-home-installment" aria-labelledby="fz-home-installment-title">
        <div class="fz-container">
            <div class="fz-home-installment__panel">
                <div class="fz-home-installment__intro">
                    <p class="fz-home-eyebrow">خرید اقساطی</p>
                    <h2 id="fz-home-installment-title">خرید را در سه قدم انجام دهید.</h2>
                    <p>شرایط زیر از تنظیمات یک محصول فعال و موجود خوانده شده است؛ جزئیات هر مدل در صفحه همان محصول نمایش داده می‌شود.</p>
                    <div class="fz-home-installment__terms">
                        <strong>{{ $installmentProduct->name }}</strong>
                        <span>
                            پیش‌پرداخت {{ \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_cash_percent) }}٪
                            ({{ \App\Helpers\PersianNumberHelper::format($installmentCashAmount) }} تومان)
                            · {{ \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_cheque_count) }} چک
                            · فاصله سررسید {{ \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_interval_months) }} ماه
                        </span>
                    </div>
                    <a href="{{ route('product.show', $installmentProduct->slug) }}">مشاهده شرایط این محصول <span aria-hidden="true">←</span></a>
                </div>

                <ol class="fz-home-installment__steps">
                    <li><span>۰۱</span><strong>انتخاب محصول</strong><p>مدل واجد شرایط را از کاتالوگ انتخاب کنید.</p></li>
                    <li><span>۰۲</span><strong>پرداخت پیش‌پرداخت</strong><p>مبلغ اولیه مطابق درصد ثبت‌شده پرداخت می‌شود.</p></li>
                    <li><span>۰۳</span><strong>ثبت چک‌ها</strong><p>چک‌ها طبق فاصله سررسید مشخص‌شده ثبت می‌شوند.</p></li>
                </ol>
            </div>
        </div>
    </section>
@endif

@if($showTrust && $hasTrustDetails)
    <section class="fz-home-section fz-home-proof" aria-labelledby="fz-home-proof-title">
        <div class="fz-container">
            <header class="fz-home-section__heading">
                <div>
                    <p class="fz-home-eyebrow">اطلاعات واقعی فروشگاه</p>
                    <h2 id="fz-home-proof-title">پیش از سفارش، راه‌های ارتباط و شرایط را ببینید.</h2>
                </div>
            </header>

            <div class="fz-home-proof-grid">
                @if($warrantyText !== '')
                    <article class="fz-home-proof-card">
                        <span class="fz-home-proof-card__icon" aria-hidden="true">✓</span>
                        <h3>گارانتی و ضمانت</h3>
                        <p>{{ $warrantyText }}</p>
                    </article>
                @endif

                @if($whatsappDigits || $phone)
                    <article class="fz-home-proof-card">
                        <span class="fz-home-proof-card__icon" aria-hidden="true">⌁</span>
                        <h3>پشتیبانی فرزین</h3>
                        <p>برای پرسش درباره مدل، موجودی و سفارش با پشتیبانی در ارتباط باشید.</p>
                        @if($whatsappDigits)
                            <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer">گفت‌وگو در واتساپ</a>
                        @endif
                        @if($phone)
                            <a href="{{ $phone['href'] }}" dir="ltr">{{ $phone['display'] }}</a>
                        @endif
                    </article>
                @endif

                @if($enamadUrl !== '')
                    <article class="fz-home-proof-card">
                        <span class="fz-home-proof-card__icon" aria-hidden="true">◆</span>
                        <h3>نماد اعتماد الکترونیکی</h3>
                        <p>برای بررسی اطلاعات و وضعیت نماد، به صفحه رسمی آن مراجعه کنید.</p>
                        <a href="{{ $enamadUrl }}" target="_blank" rel="noopener noreferrer">مشاهده نماد اعتماد</a>
                    </article>
                @endif

                @if($address !== '' || $phone)
                    <article class="fz-home-proof-card">
                        <span class="fz-home-proof-card__icon" aria-hidden="true">⌖</span>
                        <h3>نشانی و تماس</h3>
                        @if($address)<p>{{ $address }}</p>@endif
                        @if($phone)<a href="{{ $phone['href'] }}" dir="ltr">{{ $phone['display'] }}</a>@endif
                    </article>
                @endif
            </div>
        </div>
    </section>
@endif

@if($showGuides)
    <section class="fz-home-section fz-home-guides" aria-labelledby="fz-home-guides-title">
        <div class="fz-container">
            <header class="fz-home-section__heading">
                <div>
                    <p class="fz-home-eyebrow">راهنمای انتخاب</p>
                    <h2 id="fz-home-guides-title">{{ $homeCopy['page_home_guides_title'] ?? 'برای انتخاب درست، این دو راهنما را ببینید.' }}</h2>
                    <p>{{ $homeCopy['page_home_guides_description'] ?? 'نکات مهم انتخاب هود و سینک را پیش از سفارش بررسی کنید.' }}</p>
                </div>
            </header>
            <div class="fz-home-guide-grid">
                <article class="fz-home-guide-card">
                    <span class="fz-home-guide-card__number">۰۱</span>
                    <div>
                        <h3>{{ $homeCopy['page_home_hood_guide_title'] ?? 'راهنمای انتخاب هود' }}</h3>
                        <p>{{ $homeCopy['page_home_hood_guide_description'] ?? 'عرض محل نصب، نوع نصب، ابعاد اجاق و مسیر خروج هوا را با فضای آشپزخانه تطبیق دهید.' }}</p>
                        <a href="{{ $hoodCategory ? route('categories.show', $hoodCategory->slug) : route('categories.index') }}">{{ $homeCopy['page_home_hood_guide_cta'] ?? 'مشاهده هودها' }} <span aria-hidden="true">←</span></a>
                    </div>
                </article>
                <article class="fz-home-guide-card">
                    <span class="fz-home-guide-card__number">۰۲</span>
                    <div>
                        <h3>{{ $homeCopy['page_home_sink_guide_title'] ?? 'راهنمای انتخاب سینک' }}</h3>
                        <p>{{ $homeCopy['page_home_sink_guide_description'] ?? 'ابعاد کابینت، روش نصب، تعداد لگن و جنس سینک را پیش از خرید بررسی کنید.' }}</p>
                        <a href="{{ $sinkCategory ? route('categories.show', $sinkCategory->slug) : route('categories.index') }}">{{ $homeCopy['page_home_sink_guide_cta'] ?? 'مشاهده سینک‌ها' }} <span aria-hidden="true">←</span></a>
                    </div>
                </article>
            </div>
        </div>
    </section>
@endif

@if($showJournal && $blogPosts->isNotEmpty())
    <section class="fz-home-section fz-home-journal" aria-labelledby="fz-home-journal-title">
        <div class="fz-container">
            <header class="fz-home-section__heading">
                <div>
                    <p class="fz-home-eyebrow">مجله فرزین</p>
                    <h2 id="fz-home-journal-title">{{ $homeCopy['page_home_journal_title'] ?? 'پیش از انتخاب، بیشتر بدانید.' }}</h2>
                    <p>{{ $homeCopy['page_home_journal_description'] ?? 'سه مقاله کاربردی درباره انتخاب و نگهداری تجهیزات آشپزخانه.' }}</p>
                </div>
                <a class="fz-home-text-link" href="{{ route('blog.index') }}">همه مقاله‌ها <span aria-hidden="true">←</span></a>
            </header>
            <div class="fz-home-journal-grid">
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
                            @if($article->excerpt)<p>{{ \Illuminate\Support\Str::limit($article->excerpt, 125) }}</p>@endif
                            <a class="fz-home-article-card__link" href="{{ route('blog.show', $article->slug) }}">ادامه مطلب <span aria-hidden="true">←</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($showFinalCta)
    <section class="fz-home-final" aria-labelledby="fz-home-final-title">
        <div class="fz-container">
            <div class="fz-home-final__panel">
                <h2 id="fz-home-final-title">{{ $homeCopy['page_home_final_title'] ?? 'برای آشپزخانه‌تان انتخابی مطمئن داشته باشید.' }}</h2>
                <div class="fz-home-final__actions">
                    <a class="fz-sales-button fz-sales-button--light" href="{{ route('shop.index') }}">{{ $homeCopy['page_home_final_cta'] ?? 'مشاهده محصولات' }}</a>
                    @if($whatsappDigits)
                        <a class="fz-sales-button fz-sales-button--outline" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer">پرسش در واتساپ</a>
                    @elseif($phone)
                        <a class="fz-sales-button fz-sales-button--outline" href="{{ $phone['href'] }}">پرسش از پشتیبانی</a>
                    @else
                        <a class="fz-sales-button fz-sales-button--outline" href="{{ route('contact') }}">پرسش از فرزین</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
@endsection
