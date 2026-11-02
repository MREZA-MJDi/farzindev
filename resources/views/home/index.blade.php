@extends('layouts.app')

@section('title', 'فرزین | خرید آنلاین هود و سینک آشپزخانه')
@section('description', 'مدل‌های هود و سینک فرزین را با تصویر، قیمت و مشخصات بررسی کنید؛ محصولات موجود را مقایسه کنید و سفارش خود را آنلاین ثبت کنید.')
@section('canonical', route('home'))
@section('body_class', 'home-body min-h-screen antialiased')

@push('styles')
    @vite('resources/css/home.css')
@endpush

@section('content')
@php
    $homeCopy = $storefrontPageContent ?? [];
    $homeSettings = $storefrontPublicSettings ?? [];

    $heroImageUrl = trim((string) ($homeCopy['page_home_hero_image_url'] ?? ''));
    if ($heroImageUrl !== '' && ! \Illuminate\Support\Str::startsWith($heroImageUrl, ['http://', 'https://', '//'])) {
        $heroImageUrl = preg_replace('#^public/#', '', $heroImageUrl) ?? $heroImageUrl;
        $heroImageUrl = asset(ltrim($heroImageUrl, '/'));
    }
    $heroImageAlt = 'فضای آشپزخانه با هود و سینک فرزین';

    $phone = \App\Support\StorefrontContact::phone($contactSetting?->phone);
    $whatsappDigits = \App\Support\StorefrontContact::whatsapp($contactSetting?->whatsapp);
    $address = trim((string) ($contactSetting?->address ?? ''));

    $shippingFlatRate = data_get($shippingSettings, 'shipping_flat_rate');
    $freeShippingThreshold = data_get($shippingSettings, 'shipping_free_threshold');
    $shippingText = trim((string) ($homeSettings['shipping_text'] ?? ''));
    if ($shippingText === '') {
        if (is_numeric($shippingFlatRate)) {
            $shippingText = (float) $shippingFlatRate > 0
                ? 'هزینه پایه ارسال: ' . \App\Helpers\PersianNumberHelper::format($shippingFlatRate) . ' تومان'
                : 'هزینه پایه ارسال طبق تنظیمات فروشگاه رایگان است.';
        } elseif (is_numeric($freeShippingThreshold) && (float) $freeShippingThreshold > 0) {
            $shippingText = 'ارسال رایگان برای سفارش‌های بالاتر از ' . \App\Helpers\PersianNumberHelper::format($freeShippingThreshold) . ' تومان';
        } else {
            $shippingText = 'هزینه و زمان ارسال پیش از نهایی‌کردن سفارش قابل استعلام است.';
        }
    }

    $installationText = trim((string) ($homeSettings['installation_text'] ?? ''));
    if ($installationText === '') {
        $installationText = 'امکان و هزینه نصب را متناسب با شهر و مدل انتخابی، پیش از ثبت سفارش استعلام کنید.';
    }

    $warrantyText = trim((string) ($homeSettings['warranty_text'] ?? ''));
    if ($warrantyText === '') {
        $warrantyText = trim((string) ($homeCopy['page_home_warranty_text'] ?? ''));
    }
    if ($warrantyText === '') {
        $warrantyText = 'مدت و شرایط ضمانت هر مدل را پیش از خرید از پشتیبانی فرزین استعلام کنید.';
    }

    $installmentText = $installmentProduct
        ? $installmentProduct->name . ' با ' .
            \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_cash_percent) . '٪ پیش‌پرداخت و ' .
            \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_cheque_count) . ' چک؛ فاصله سررسید ' .
            \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_interval_months) . ' ماه.'
        : 'شرایط اقساطی فقط برای محصولاتی نمایش داده می‌شود که این امکان در صفحه همان محصول فعال شده باشد.';

    $enamadUrl = trim((string) ($homeCopy['page_home_enamad_url'] ?? ''));
    $reviewText = trim((string) ($homeCopy['page_home_reviews_text'] ?? ''));
    $showCategories = ($homeCopy['page_home_show_categories'] ?? '1') === '1';
    $showProducts = ($homeCopy['page_home_show_products'] ?? '1') === '1';
    $showTrust = ($homeCopy['page_home_show_trust'] ?? '1') === '1';
    $showGuides = ($homeCopy['page_home_show_guides'] ?? '1') === '1';
    $showJournal = ($homeCopy['page_home_show_journal'] ?? '1') === '1';
    $showFinalCta = ($homeCopy['page_home_show_final_cta'] ?? '1') === '1';

    $homeHeroTitle = trim((string) ($homeCopy['page_home_hero_title'] ?? '')) ?: 'هود و سینک مناسب آشپزخانه‌تان را';
    $homeHeroHighlight = trim((string) ($homeCopy['page_home_hero_highlight'] ?? '')) ?: 'مستقیم از فرزین انتخاب کنید';
    $homeHeroDescription = trim((string) ($homeCopy['page_home_hero_description'] ?? '')) ?: 'مدل‌های موجود را با قیمت و مشخصات ببینید، گزینه‌ها را مقایسه کنید و سفارش خود را آنلاین ثبت کنید.';
@endphp

<section class="fz-sales-hero" aria-labelledby="fz-sales-hero-title">
    <div class="fz-container fz-sales-hero__layout">
        <div class="fz-sales-hero__copy">
            <p class="fz-sales-hero__eyebrow"><span aria-hidden="true"></span>{{ $homeCopy['page_home_hero_eyebrow'] ?? 'تجهیزات آشپزخانه فرزین' }}</p>
            <h1 id="fz-sales-hero-title">
                {{ $homeHeroTitle }}
                <strong>{{ $homeHeroHighlight }}</strong>
            </h1>
            <p class="fz-sales-hero__lead">{{ $homeHeroDescription }}</p>
            <div class="fz-sales-hero__actions">
                <a class="fz-sales-button fz-sales-button--primary" href="{{ route('shop.index') }}">
                    {{ $homeCopy['page_home_hero_primary_label'] ?? 'مشاهده محصولات' }}
                    <span aria-hidden="true">←</span>
                </a>
                <a class="fz-sales-button fz-sales-button--secondary" href="{{ route('contact') }}">
                    {{ $homeCopy['page_home_hero_secondary_label'] ?? 'مشاوره پیش از خرید' }}
                </a>
            </div>
            <div class="fz-sales-hero__meta">
                <span><b aria-hidden="true">✓</b> مشاهده قیمت و موجودی ثبت‌شده</span>
                <span><b aria-hidden="true">✓</b> سفارش آنلاین و پیگیری از حساب کاربری</span>
            </div>
        </div>

        <div class="fz-sales-hero__visual">
            @if($heroImageUrl !== '')
                <img
                    src="{{ $heroImageUrl }}"
                    alt="{{ $heroImageAlt }}"
                    width="1400"
                    height="1050"
                    fetchpriority="high"
                    loading="eager"
                    decoding="async"
                >
                <span class="fz-sales-hero__image-caption">فضای آشپزخانه · هود و سینک</span>
            @else
                <div class="fz-sales-hero__image-placeholder" role="img" aria-label="جایگاه تصویر واقعی آشپزخانه">
                    <span class="fz-sales-hero__placeholder-kicker">FARZIN · KITCHEN EQUIPMENT</span>
                    <span class="fz-sales-hero__placeholder-title">تصویر واقعی آشپزخانه</span>
                    <span class="fz-sales-hero__placeholder-copy">عکس منتخب پروژه را از مدیریت محتوا در فیلد تصویر Hero بارگذاری کنید.</span>
                    <span class="fz-sales-hero__placeholder-shape fz-sales-hero__placeholder-shape--one" aria-hidden="true"></span>
                    <span class="fz-sales-hero__placeholder-shape fz-sales-hero__placeholder-shape--two" aria-hidden="true"></span>
                </div>
                <span class="fz-sales-hero__image-caption">جایگاه تصویر قابل تنظیم از داشبورد</span>
            @endif
        </div>
    </div>
</section>

<section class="fz-home-benefits" aria-label="مزیت‌ها و اطلاعات خرید">
    <div class="fz-container fz-home-benefits__grid">
        <article class="fz-home-benefit">
            <span class="fz-home-benefit__icon" aria-hidden="true">↗</span>
            <div><strong>ارسال سفارش</strong><p>{{ $shippingText }}</p></div>
        </article>
        <article class="fz-home-benefit">
            <span class="fz-home-benefit__icon" aria-hidden="true">⌂</span>
            <div><strong>نصب و راه‌اندازی</strong><p>{{ $installationText }}</p></div>
        </article>
        <article class="fz-home-benefit">
            <span class="fz-home-benefit__icon" aria-hidden="true">✓</span>
            <div><strong>ضمانت محصول</strong><p>{{ $warrantyText }}</p></div>
        </article>
        <article class="fz-home-benefit">
            <span class="fz-home-benefit__icon" aria-hidden="true">٪</span>
            <div><strong>پرداخت اقساطی</strong><p>{{ $installmentProduct ? 'برای مدل‌های واجد شرایط؛ جزئیات هر مدل در صفحه محصول.' : 'در صورت فعال‌بودن، شرایط دقیق در صفحه همان محصول درج می‌شود.' }}</p></div>
        </article>
    </div>
</section>

@if($showCategories && $categories->isNotEmpty())
    <section id="product-categories" class="fz-home-section fz-home-categories" aria-labelledby="fz-home-categories-title">
        <div class="fz-container">
            <header class="fz-home-section__heading">
                <div>
                    <p class="fz-home-eyebrow">{{ $homeCopy['page_home_categories_eyebrow'] ?? 'دسته‌بندی محصولات' }}</p>
                    <h2 id="fz-home-categories-title">{{ $homeCopy['page_home_categories_title'] ?? 'از دسته‌ی موردنیازتان شروع کنید.' }}</h2>
                    <p>{{ $homeCopy['page_home_categories_description'] ?? 'فقط دسته‌هایی را می‌بینید که محصول فعال دارند.' }}</p>
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

@if($showProducts && $featuredProducts->isNotEmpty())
    <section class="fz-home-section fz-home-products" aria-labelledby="fz-home-products-title">
        <div class="fz-container">
            <header class="fz-home-section__heading">
                <div>
                    <p class="fz-home-eyebrow">{{ $homeCopy['page_home_products_eyebrow'] ?? 'منتخب فرزین' }}</p>
                    <h2 id="fz-home-products-title">{{ $homeCopy['page_home_products_title'] ?? 'مدل‌های منتخب برای خرید' }}</h2>
                    <p>{{ $homeCopy['page_home_products_description'] ?? 'ابتدا محصولات قابل خرید و سپس مدل‌های ناموجود نمایش داده می‌شوند.' }}</p>
                </div>
                <a class="fz-home-text-link" href="{{ route('shop.index') }}">مشاهده همه محصولات <span aria-hidden="true">←</span></a>
            </header>
            <div class="fz-home-product-grid">
                @foreach($featuredProducts as $product)
                    <x-product.card :product="$product" :show-availability="true" />
                @endforeach
            </div>
        </div>
    </section>
@elseif($showProducts)
    <section class="fz-home-section fz-home-products" aria-labelledby="fz-home-products-title">
        <div class="fz-container">
            <div class="fz-home-empty">
                <h2 id="fz-home-products-title">محصول منتخب برای نمایش ثبت نشده است.</h2>
                <p>به‌محض فعال‌شدن محصولات و انتخاب آن‌ها برای صفحه اصلی، مدل‌ها در این بخش نمایش داده می‌شوند.</p>
                <a class="fz-sales-button fz-sales-button--primary" href="{{ route('shop.index') }}">رفتن به فروشگاه</a>
            </div>
        </div>
    </section>
@endif

@if($installmentProduct)
    <section class="fz-home-installment" aria-labelledby="fz-home-installment-title">
        <div class="fz-container">
            <div class="fz-home-installment__panel">
                <div>
                    <p class="fz-home-eyebrow">پرداخت منعطف</p>
                    <h2 id="fz-home-installment-title">این مدل را با شرایط اقساطی ثبت‌شده بخرید.</h2>
                    <p>{{ $installmentText }}</p>
                    <small>مبلغ نهایی و شرایط هر سفارش در صفحه محصول و هنگام ثبت سفارش قابل بررسی است.</small>
                </div>
                <a href="{{ route('product.show', $installmentProduct->slug) }}">مشاهده شرایط و قیمت <span aria-hidden="true">←</span></a>
            </div>
        </div>
    </section>
@endif

@if($showTrust)
    <section class="fz-home-section fz-home-proof" aria-labelledby="fz-home-proof-title">
        <div class="fz-container">
            <header class="fz-home-section__heading">
                <div>
                    <p class="fz-home-eyebrow">{{ $homeCopy['page_home_trust_eyebrow'] ?? 'اطلاعات پیش از خرید' }}</p>
                    <h2 id="fz-home-proof-title">{{ $homeCopy['page_home_trust_title'] ?? 'با اطلاعات روشن، مطمئن‌تر انتخاب کنید.' }}</h2>
                    <p>اطلاعات واقعی مجموعه و شرایط هر محصول را قبل از سفارش بررسی کنید.</p>
                </div>
            </header>

            <div class="fz-home-proof-grid">
                <article class="fz-home-proof-card">
                    <span class="fz-home-proof-card__icon" aria-hidden="true">✓</span>
                    <h3>ضمانت و خدمات</h3>
                    <p>{{ $warrantyText }}</p>
                    @if(trim((string) ($homeSettings['returns_text'] ?? '')) !== '')
                        <small>{{ $homeSettings['returns_text'] }}</small>
                    @endif
                </article>

                <article class="fz-home-proof-card">
                    <span class="fz-home-proof-card__icon" aria-hidden="true">☎</span>
                    <h3>پشتیبانی واقعی</h3>
                    @if($phone)
                        <p>برای پرسش درباره مدل، ارسال و سفارش با شماره ثبت‌شده تماس بگیرید.</p>
                        <a href="{{ $phone['href'] }}">{{ $phone['display'] }}</a>
                    @elseif($whatsappDigits)
                        <p>برای پرسش درباره محصول و سفارش از واتساپ ثبت‌شده استفاده کنید.</p>
                        <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer">گفت‌وگو در واتساپ</a>
                    @else
                        <p>اطلاعات تماس عمومی هنوز در تنظیمات فروشگاه ثبت نشده است.</p>
                        <a href="{{ route('contact') }}">صفحه تماس با ما</a>
                    @endif
                    @if($address !== '')
                        <small>{{ $address }}</small>
                    @endif
                </article>

                <article class="fz-home-proof-card">
                    <span class="fz-home-proof-card__icon" aria-hidden="true">◆</span>
                    <h3>نماد اعتماد الکترونیکی</h3>
                    @if($enamadUrl !== '' && \Illuminate\Support\Str::startsWith($enamadUrl, ['https://']))
                        <p>برای بررسی وضعیت نماد، پیوند رسمی ثبت‌شده را مشاهده کنید.</p>
                        <a href="{{ $enamadUrl }}" target="_blank" rel="noopener noreferrer">مشاهده صفحه نماد اعتماد</a>
                    @else
                        <p>پیوند رسمی نماد اعتماد هنوز در مدیریت محتوای صفحه اصلی ثبت نشده است.</p>
                        <small>از نمایش نشان یا شماره تأییدنشده خودداری شده است.</small>
                    @endif
                </article>

                <article class="fz-home-proof-card">
                    <span class="fz-home-proof-card__icon" aria-hidden="true">★</span>
                    <h3>نظر مشتریان</h3>
                    @if($reviewText !== '')
                        <p>{{ $reviewText }}</p>
                    @else
                        <p>نظرهای تأییدشده مشتریان پس از دریافت و تأیید، در این بخش نمایش داده می‌شوند.</p>
                        <small>در حال حاضر نظر ساختگی یا امتیاز غیرواقعی نمایش داده نمی‌شود.</small>
                    @endif
                </article>
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
                    <h2 id="fz-home-guides-title">{{ $homeCopy['page_home_guides_title'] ?? 'پیش از انتخاب، این موارد را بررسی کنید.' }}</h2>
                    <p>{{ $homeCopy['page_home_guides_description'] ?? 'دو مسیر اصلی انتخاب تجهیزات آشپزخانه، در یک بخش جمع شده‌اند.' }}</p>
                </div>
            </header>
            <div class="fz-home-guide-grid">
                <article class="fz-home-guide-card">
                    <span class="fz-home-guide-card__number">۰۱</span>
                    <div>
                        <h3>{{ $homeCopy['page_home_hood_guide_title'] ?? 'برای انتخاب هود' }}</h3>
                        <p>{{ $homeCopy['page_home_hood_guide_description'] ?? 'عرض محل نصب، نوع نصب، ابعاد اجاق، خروجی هوا و سطح صدای دستگاه را با فضای آشپزخانه تطبیق دهید.' }}</p>
                        <a href="{{ route('shop.index') }}">{{ $homeCopy['page_home_hood_guide_cta'] ?? 'بررسی مدل‌های هود' }} <span aria-hidden="true">←</span></a>
                    </div>
                </article>
                <article class="fz-home-guide-card">
                    <span class="fz-home-guide-card__number">۰۲</span>
                    <div>
                        <h3>{{ $homeCopy['page_home_sink_guide_title'] ?? 'برای انتخاب سینک' }}</h3>
                        <p>{{ $homeCopy['page_home_sink_guide_description'] ?? 'ابعاد کابینت، روش نصب، تعداد لگن، جنس بدنه و محل قرارگیری شیر و تخلیه را پیش از خرید اندازه‌گیری کنید.' }}</p>
                        <a href="{{ route('shop.index') }}">{{ $homeCopy['page_home_sink_guide_cta'] ?? 'بررسی مدل‌های سینک' }} <span aria-hidden="true">←</span></a>
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
                    <h2 id="fz-home-journal-title">{{ $homeCopy['page_home_journal_title'] ?? 'برای انتخاب بهتر، بیشتر بخوانید.' }}</h2>
                    <p>{{ $homeCopy['page_home_journal_description'] ?? 'راهنماها و مقاله‌های کاربردی درباره هود و سینک آشپزخانه.' }}</p>
                </div>
                <a class="fz-home-text-link" href="{{ route('blog.index') }}">همه مقاله‌ها <span aria-hidden="true">←</span></a>
            </header>
            <div class="fz-home-journal-grid">
                @foreach($blogPosts->take(3) as $article)
                    @php($articleCategory = $article->terms->firstWhere('type', 'category'))
                    <article class="fz-home-article-card">
                        <a class="fz-home-article-card__media" href="{{ route('blog.show', $article->slug) }}" aria-label="مطالعه {{ $article->title }}">
                            @if($article->cover_image_url)
                                <img src="{{ $article->cover_image_url }}"
                                     @if($article->cover_image_srcset) srcset="{{ $article->cover_image_srcset }}" sizes="(max-width: 767px) 88vw, (max-width: 1023px) 45vw, 360px" @endif
                                     alt="{{ $article->title }}" width="900" height="620" loading="lazy" decoding="async">
                            @else
                                <span class="fz-home-article-card__fallback" aria-hidden="true">FARZIN JOURNAL</span>
                            @endif
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
                <div>
                    <p class="fz-home-eyebrow">یک قدم تا انتخاب</p>
                    <h2 id="fz-home-final-title">{{ $homeCopy['page_home_final_title'] ?? 'مدل مناسب آشپزخانه‌تان را پیدا کنید.' }}</h2>
                    <p>{{ $homeCopy['page_home_final_description'] ?? 'محصول‌ها را ببینید و برای انتخاب، نصب یا شرایط ارسال با فرزین در ارتباط باشید.' }}</p>
                </div>
                <div class="fz-home-final__actions">
                    <a class="fz-sales-button fz-sales-button--light" href="{{ route('shop.index') }}">{{ $homeCopy['page_home_final_cta'] ?? 'مشاهده محصولات' }}</a>
                    @if($phone)<a class="fz-sales-button fz-sales-button--outline" href="{{ $phone['href'] }}">تماس {{ $phone['display'] }}</a>@endif
                    @if($whatsappDigits)<a class="fz-sales-button fz-sales-button--outline" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer">واتساپ</a>@endif
                    @if(!$phone && !$whatsappDigits)<a class="fz-sales-button fz-sales-button--outline" href="{{ route('contact') }}">ارتباط با فرزین</a>@endif
                </div>
            </div>
        </div>
    </section>
@endif
@endsection
