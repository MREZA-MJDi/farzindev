@extends('layouts.app')

@section('title', 'فرزین | تولید هود و سینک آشپزخانه')
@section('description', 'تجهیزات آشپزخانه فرزین؛ تصاویر واقعی محصولات، مقایسه مشخصات هود و سینک و راهنمای انتخاب متناسب با فضای نصب.')
@section('canonical', route('home'))
@section('body_class', 'home-body min-h-screen antialiased')

@push('styles')
    @vite(['resources/css/home.css'])
@endpush

@push('scripts')
    @vite(['resources/js/home.js'])
@endpush

@section('content')
    <a class="fz-skip-link" href="#main-content">رفتن به محتوای اصلی</a>

    <section class="fz-home-hero fz-home-hero--meraki" aria-labelledby="fz-home-hero-title">
        <div class="fz-container fz-home-hero__layout">
            <div class="fz-home-hero__copy">
                <p class="fz-home-hero__eyebrow">
                    <span class="fz-home-hero__eyebrow-mark" aria-hidden="true"></span>
                    تجهیزات آشپزخانه فرزین
                </p>

                <h1 id="fz-home-hero-title">
                    <span>انتخاب دقیق‌تر برای</span>
                    <strong>آشپزخانه‌ای هماهنگ‌تر</strong>
                </h1>

                <p class="fz-home-hero__lead">
                    هود و سینک مناسب فضای خود را پیدا کنید؛ تصاویر واقعی محصولات را ببینید، مشخصات را مقایسه کنید و با آگاهی بیشتری تصمیم بگیرید.
                </p>

                <div class="fz-home-hero__actions">
                    <a class="fz-home-hero__button fz-home-hero__button--primary" href="{{ route('shop.index') }}">
                        مشاهده کاتالوگ محصولات
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5m7 7-7-7 7-7" />
                        </svg>
                    </a>
                    <a class="fz-home-hero__button fz-home-hero__button--secondary" href="{{ route('contact') }}">
                        مشاوره خرید
                    </a>
                </div>

                <div class="fz-home-hero__highlights" aria-label="مزیت‌های انتخاب آگاهانه">
                    <span><b aria-hidden="true">۰۱</b> بررسی ابعاد و نصب</span>
                    <span><b aria-hidden="true">۰۲</b> مقایسه مشخصات</span>
                    <span><b aria-hidden="true">۰۳</b> انتخاب متناسب با فضا</span>
                </div>
            </div>

            @if($heroProducts->isNotEmpty())
                <div
                    class="fz-home-hero__showcase fz-home-hero__showcase--product"
                    data-farzin-home-slider
                    data-autoplay-ms="4500"
                    role="region"
                    aria-label="تصاویر محصولات فرزین"
                    aria-roledescription="carousel"
                >
                    <div class="fz-home-hero__showcase-heading">
                        <div>
                            <p>محصولات فرزین</p>
                            <span>تصویر و مشخصات مدل‌های منتخب</span>
                        </div>
                        <span class="fz-home-hero__counter" data-home-slide-count aria-live="off">
                            {{ \App\Helpers\PersianNumberHelper::format(1) }}
                            <i>/</i>
                            {{ \App\Helpers\PersianNumberHelper::format($heroProducts->count()) }}
                        </span>
                    </div>

                    <div class="fz-home-hero__slide-stage" data-home-slide-track>
                        @foreach($heroProducts as $index => $product)
                            @php
                                $heroImage = $product->images->first(fn ($image) => filled($image->url));
                            @endphp
                            @if($heroImage)
                                <a
                                    href="{{ route('product.show', $product->slug) }}"
                                    class="fz-home-hero__slide"
                                    data-home-slide
                                    data-home-product-id="{{ $product->id }}"
                                    role="group"
                                    aria-roledescription="اسلاید"
                                    aria-label="{{ \App\Helpers\PersianNumberHelper::format($index + 1) }} از {{ \App\Helpers\PersianNumberHelper::format($heroProducts->count()) }}: {{ $product->name }}"
                                    @if($index === 0) aria-hidden="false" @else hidden aria-hidden="true" @endif
                                >
                                    <span class="fz-home-hero__product-image">
                                        <img
                                            src="{{ $heroImage->url }}"
                                            @if($heroImage->responsive_srcset) srcset="{{ $heroImage->responsive_srcset }}" sizes="(max-width: 767px) 92vw, 48vw" @endif
                                            alt="{{ $heroImage->alt ?: $product->name }}"
                                            width="960"
                                            height="720"
                                            decoding="async"
                                            @if($index === 0)
                                                fetchpriority="high"
                                                loading="eager"
                                            @else
                                                loading="lazy"
                                            @endif
                                        >
                                        <span class="fz-home-hero__product-open" aria-hidden="true">↗</span>
                                    </span>
                                    <span class="fz-home-hero__product-details">
                                        <span class="fz-home-hero__product-category">{{ $product->category?->name ?? 'محصول فرزین' }}</span>
                                        <strong>{{ $product->name }}</strong>
                                        <span class="fz-home-hero__product-footer">
                                            <span class="fz-home-hero__product-price">
                                                {{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}
                                                <small>تومان</small>
                                            </span>
                                            <span class="fz-home-hero__product-link">مشاهده مشخصات</span>
                                        </span>
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </div>

                    @if($heroProducts->count() > 1)
                        <div class="fz-home-hero__slider-controls" data-home-slider-controls>
                            <div class="fz-home-hero__slider-dots" role="group" aria-label="انتخاب محصول">
                                @foreach($heroProducts as $index => $product)
                                    <button
                                        type="button"
                                        class="fz-home-hero__slider-dot {{ $index === 0 ? 'is-active' : '' }}"
                                        data-home-slide-to="{{ $index }}"
                                        aria-label="نمایش محصول {{ \App\Helpers\PersianNumberHelper::format($index + 1) }}"
                                        aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                                    ></button>
                                @endforeach
                            </div>

                            <div class="fz-home-hero__slider-actions">
                                <button type="button" data-home-slide-prev aria-label="محصول قبلی">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7" /></svg>
                                </button>
                                <button type="button" data-home-slide-next aria-label="محصول بعدی">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7" /></svg>
                                </button>
                                <button type="button" class="fz-home-hero__slider-toggle" data-home-slide-toggle aria-label="توقف پخش خودکار" title="توقف پخش خودکار">
                                    <span data-home-slide-toggle-label>مکث</span>
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5h3v14H8zM14 5h3v14h-3z" /></svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <a class="fz-home-hero__showcase fz-home-hero__showcase--empty" href="{{ route('shop.index') }}">
                    <span class="fz-home-hero__empty-mark" aria-hidden="true">FZ</span>
                    <span>
                        <strong>کاتالوگ محصولات فرزین</strong>
                        <small>برای دیدن تصاویر، محصولات فعال همراه تصویر را در فروشگاه منتشر کنید.</small>
                    </span>
                </a>
            @endif
        </div>

        <div class="fz-container fz-home-hero__bottom">
            <span>FARZIN / KITCHEN EQUIPMENT</span>
            <span>هود آشپزخانه <i>·</i> سینک ظرف‌شویی</span>
            <a href="#product-categories">مشاهده دسته‌بندی‌ها <b aria-hidden="true">↓</b></a>
        </div>
    </section>

    @if($categories->isNotEmpty())
        <section id="product-categories" class="fz-section fz-category-section" aria-labelledby="fz-category-heading">
            <div class="fz-container">
                <div class="fz-category-heading fz-reveal">
                    <div>
                        <p class="fz-eyebrow"><span></span> مسیر انتخاب</p>
                        <h2 id="fz-category-heading">از دسته‌ی مناسب شروع کنید.</h2>
                        <p>دسته‌بندی موردنظرتان را انتخاب کنید و مدل‌ها را با هم مقایسه کنید.</p>
                    </div>
                    <a class="fz-text-link" href="{{ route('categories.index') }}">همه دسته‌بندی‌ها <span aria-hidden="true">←</span></a>
                </div>

                <div class="fz-category-grid fz-category-grid--three">
                    @foreach($categories as $index => $category)
                        <x-home.category-card :category="$category" :index="$index" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($featuredProducts->isNotEmpty())
        <section class="fz-section fz-product-section" aria-labelledby="fz-products-heading">
            <div class="fz-container">
                <div class="fz-section-heading fz-reveal">
                    <div>
                        <p class="fz-eyebrow"><span></span> انتخاب مشتریان</p>
                        <h2 id="fz-products-heading">{{ $hasBestSellerSales ? 'پرفروش‌ترین محصولات' : 'مدل‌های منتخب فرزین' }}</h2>
                        <p>
                            @if($hasBestSellerSales)
                                رتبه‌بندی بر اساس سفارش‌های پرداخت‌شده؛ برای جزئیات هر مدل وارد صفحه محصول شوید.
                            @else
                                تا زمان ثبت سفارش پرداخت‌شده، محصولات منتخب‌شده در پنل مدیریت را بررسی کنید.
                            @endif
                        </p>
                    </div>
                    <a class="fz-text-link" href="{{ route('shop.index') }}">مشاهده همه محصولات <span aria-hidden="true">←</span></a>
                </div>
                <div class="fz-product-grid fz-product-grid--home">
                    @foreach($featuredProducts as $product)
                        <x-home.product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="fz-section fz-buying-guide" aria-labelledby="fz-guide-heading">
        <div class="fz-container">
            <div class="fz-section-heading fz-reveal">
                <div>
                    <p class="fz-eyebrow"><span></span> راهنمای خرید</p>
                    <h2 id="fz-guide-heading">قبل از انتخاب، این سه نکته را بررسی کنید.</h2>
                    <p>چند معیار ساده کمک می‌کند محصول مناسب‌تری برای فضای آشپزخانه انتخاب کنید.</p>
                </div>
            </div>

            <div class="fz-guide-grid">
                <x-home.feature-card
                    number="۰۱"
                    title="ابعاد و محل نصب"
                    description="عرض کابینت، فضای برش، فاصله از اجاق و مسیر خروجی را پیش از انتخاب اندازه بگیرید."
                    href="{{ route('categories.index') }}"
                    action="بررسی دسته‌بندی‌ها"
                    icon="ruler"
                />
                <x-home.feature-card
                    number="۰۲"
                    title="جنس و نگهداری"
                    description="جنس بدنه، عمق لگن و روش نظافت را با میزان استفاده روزانه و شرایط آشپزخانه تطبیق دهید."
                    href="{{ route('shop.index') }}"
                    action="مقایسه محصولات"
                    icon="layers"
                />
                <x-home.feature-card
                    number="۰۳"
                    title="مشخصات و مشاوره"
                    description="مشخصات فنی هر مدل را بخوانید؛ اگر برای انتخاب تردید دارید از تیم فرزین راهنمایی بگیرید."
                    href="{{ route('contact') }}"
                    action="دریافت راهنمایی"
                    icon="messages"
                />
            </div>
        </div>
    </section>

    @if($blogPosts->isNotEmpty())
        <section class="fz-section fz-home-journal" aria-labelledby="fz-journal-heading">
            <div class="fz-container">
                <div class="fz-section-heading fz-reveal">
                    <div>
                        <p class="fz-eyebrow"><span></span> مجله فرزین</p>
                        <h2 id="fz-journal-heading">راهنماهایی برای انتخاب آگاهانه‌تر.</h2>
                        <p>نوشته‌های منتشرشده درباره انتخاب، نصب و نگهداری تجهیزات آشپزخانه.</p>
                    </div>
                    <a class="fz-text-link" href="{{ route('blog.index') }}">رفتن به مجله <span aria-hidden="true">←</span></a>
                </div>

                <div class="fz-journal-grid">
                    @foreach($blogPosts as $post)
                        <x-home.article-card :post="$post" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="fz-bottom-cta" aria-labelledby="fz-bottom-cta-title">
        <div class="fz-container fz-bottom-cta__inner fz-reveal">
            <div>
                <p class="fz-eyebrow"><span></span> قدم بعدی</p>
                <h2 id="fz-bottom-cta-title">برای آشپزخانه‌تان انتخاب دقیق‌تری داشته باشید.</h2>
                <p>محصولات را بررسی کنید یا برای راهنمایی درباره مشخصات و شرایط خرید با ما در تماس باشید.</p>
            </div>
            <div class="fz-hero__actions">
                <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}">مشاهده محصولات</a>
                <a class="fz-button fz-button--secondary" href="{{ route('contact') }}">تماس با فرزین</a>
            </div>
        </div>
    </section>

    <div id="toast-root" class="toast-root" aria-live="polite" aria-atomic="true"></div>
@endsection
