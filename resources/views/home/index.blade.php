@extends('layouts.app')

@section('title', 'فرزین | تولید هود و سینک آشپزخانه')
@section('description', 'آشنایی با فرزین و تجهیزات آشپزخانه؛ هود و سینک را ببینید، مشخصات محصولات را مقایسه کنید و برای انتخابی متناسب با فضای نصب راهنمایی بگیرید.')
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

@php
    $homeCtaCategory = $categories->first(
        fn ($category) => filled($category->homepage_image_url)
    );

    $homeCtaProduct = collect([
        $featuredProducts->first(),
        $newProducts->first(),
        $heroProducts->first(),
    ])->filter()->first(
        fn ($product) => $product->images->contains(
            fn ($image) => filled($image->url)
        )
    );

    $homeCtaProductImage = $homeCtaProduct?->images->first(
        fn ($image) => filled($image->url)
    );

    $homeCtaImageUrl = $homeCtaCategory?->homepage_image_url
        ?? $homeCtaProductImage?->url;
    $homeCtaImageSrcset = $homeCtaCategory?->homepage_image_srcset
        ?? $homeCtaProductImage?->responsive_srcset;
@endphp

<section class="fz-home-hero" aria-labelledby="fz-home-hero-title">
    <div class="fz-home-hero__ambient" aria-hidden="true">
        <span class="fz-home-hero__ambient-orb fz-home-hero__ambient-orb--one"></span>
        <span class="fz-home-hero__ambient-orb fz-home-hero__ambient-orb--two"></span>
        <span class="fz-home-hero__ambient-grid"></span>
    </div>

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
                مدل‌های هود و سینک را ببینید، تصاویر و مشخصات را مقایسه کنید و محصولی متناسب با فضای نصب و نیاز روزمره‌تان انتخاب کنید.
            </p>

            <div class="fz-home-hero__actions">
                <a class="fz-home-hero__button fz-home-hero__button--primary" href="{{ route('shop.index') }}">
                    مشاهده کاتالوگ محصولات
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5m7 7-7-7 7-7" />
                    </svg>
                </a>
            </div>

            <div class="fz-home-hero__highlights" aria-label="مزیت‌های خرید از کاتالوگ فرزین">
                <span><b aria-hidden="true">✓</b> هود و سینک در یک کاتالوگ</span>
                <span><b aria-hidden="true">✓</b> تصویر و مشخصات هر مدل</span>
                <span><b aria-hidden="true">✓</b> ثبت سفارش آنلاین</span>
            </div>
        </div>

        @if($heroProducts->isNotEmpty())
            <div
                class="fz-home-hero__showcase"
                data-farzin-home-slider
                data-autoplay-ms="3600"
                role="region"
                aria-label="تازه‌ترین محصولات فرزین"
                aria-roledescription="carousel"
            >
                <div class="fz-home-hero__showcase-heading">
                    <div>
                        <p>تازه‌ترین محصولات</p>
                        <span>مدل‌های تازه‌اضافه‌شده به کاتالوگ فرزین</span>
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
                                        @if($heroImage->responsive_srcset) srcset="{{ $heroImage->responsive_srcset }}" sizes="(max-width: 767px) 92vw, 52vw" @endif
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
                                    <span class="fz-home-hero__product-badge">تازه در فروشگاه</span>
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
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M19 12H5m7 7-7-7 7-7" />
                                </svg>
                            </button>
                            <button type="button" data-home-slide-next aria-label="محصول بعدی">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14m-7-7 7 7-7 7" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                class="fz-home-hero__slider-toggle"
                                data-home-slide-toggle
                                aria-label="توقف پخش خودکار"
                                title="توقف پخش خودکار"
                            >
                                <span data-home-slide-toggle-label>مکث</span>
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M8 5h3v14H8zM14 5h3v14h-3z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="fz-home-hero__showcase fz-home-hero__showcase--empty">
                <span class="fz-home-hero__empty-mark" aria-hidden="true">FZ</span>
                <div>
                    <p>کاتالوگ محصولات فرزین</p>
                    <strong>مدل‌های هود و سینک را یک‌جا ببینید.</strong>
                    <span>برای نمایش تصویر در این بخش، محصول فعال همراه با تصویر در فروشگاه منتشر کنید.</span>
                    <a href="{{ route('shop.index') }}">رفتن به فروشگاه</a>
                </div>
            </div>
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
                        <p class="fz-eyebrow"><span></span> دسته‌بندی محصولات فرزین</p>
                        <h2 id="fz-category-heading">از دسته‌ی مناسب شروع کنید.</h2>
                        <p>هود و سینک موردنیازتان را از میان دسته‌بندی‌های محصولات فرزین پیدا کنید.</p>
                        <a class="fz-category-heading__link fz-text-link" href="{{ route('categories.index') }}">
                            مشاهده همه دسته‌بندی‌ها
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 12H5m7 7-7-7 7-7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="fz-category-grid">
                        @foreach($categories as $index => $category)
                            <a
                                class="fz-category-card fz-reveal"
                                style="--fz-category-delay: {{ $index * 90 }}ms"
                                href="{{ route('categories.show', $category) }}"
                                @if($category->hoverMedia?->url) data-farzin-gif-card @endif
                                aria-label="مشاهده دسته‌بندی {{ $category->name }}، {{ \App\Helpers\PersianNumberHelper::format($category->products_count) }} محصول فعال"
                            >
                                <span class="fz-category-card__art">
                                    @if($category->homepage_image_url)
                                        <img
                                            class="fz-category-card__image"
                                            src="{{ $category->homepage_image_url }}"
                                            @if($category->homepage_image_srcset) srcset="{{ $category->homepage_image_srcset }}" sizes="(max-width: 639px) 88vw, (max-width: 1023px) 42vw, (max-width: 1279px) 28vw, 260px" @endif
                                            alt="{{ $category->name }}"
                                            width="640"
                                            height="480"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @else
                                        <span class="fz-category-card__monogram" aria-hidden="true">{{ $category->slug === 'sinks' ? 'S' : 'H' }}</span>
                                    @endif

                                    @if($category->hoverMedia?->is_video)
                                        <video
                                            data-farzin-category-video
                                            data-farzin-video-src="{{ $category->hoverMedia->url }}"
                                            class="fz-category-card__gif"
                                            muted
                                            loop
                                            playsinline
                                            preload="none"
                                            aria-hidden="true"
                                            tabindex="-1"
                                        ></video>
                                    @elseif($category->hoverMedia?->mime_type === 'image/gif')
                                        <img
                                            data-farzin-category-gif
                                            data-farzin-gif-src="{{ $category->hoverMedia->url }}"
                                            class="fz-category-card__gif"
                                            alt=""
                                            aria-hidden="true"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @endif

                                    
                                </span>

                                <span class="fz-category-card__content">
                                    <span class="fz-category-card__eyebrow">FARZIN / CATEGORY</span>
                                    <strong>{{ $category->name }}</strong>
                                    <small>{{ \App\Helpers\PersianNumberHelper::format($category->products_count) }} محصول فعال</small>
                                    <span class="fz-category-card__link">
                                        مشاهده محصولات
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M19 12H5m7 7-7-7 7-7"/>
                                        </svg>
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($featuredProducts->isNotEmpty())
            @php
                $featuredBackdropCategory = $categories->first(
                    fn ($category) => filled($category->homepage_image_url)
                );
                $featuredBackdropProductImage = $featuredProducts->first()?->images->first(
                    fn ($image) => filled($image->url)
                );
                $featuredBackdropImage = $featuredBackdropCategory?->homepage_image_url
                    ?? $featuredBackdropProductImage?->url;
                $featuredBackdropSrcset = $featuredBackdropCategory?->homepage_image_srcset
                    ?? $featuredBackdropProductImage?->responsive_srcset;
            @endphp

            <section
                class="fz-section fz-product-section fz-home-featured-section"
                aria-labelledby="fz-products-heading"
            >
                <div class="fz-home-featured-section__visual" aria-hidden="true">
                    @if($featuredBackdropImage)
                        <img
                            src="{{ $featuredBackdropImage }}"
                            @if($featuredBackdropSrcset) srcset="{{ $featuredBackdropSrcset }}" sizes="(min-width: 1280px) 36vw, (min-width: 768px) 42vw, 100vw" @endif
                            alt=""
                            width="1200"
                            height="900"
                            loading="lazy"
                            decoding="async"
                        >
                    @endif
                    <span class="fz-home-featured-section__visual-shade"></span>
                </div>

                <div class="fz-container fz-home-featured-section__inner">
                    <header class="fz-home-featured-section__heading fz-reveal">
                        <div class="fz-home-featured-section__heading-copy">
                            <p class="fz-eyebrow">
                                <span aria-hidden="true"></span>
                                کاتالوگ محصولات فرزین
                            </p>

                            <h2 id="fz-products-heading">
                                {{ $hasBestSellerSales ? 'پرفروش‌ترین محصولات' : 'مدل‌های منتخب فرزین' }}
                            </h2>

                            <p>
                                @if($hasBestSellerSales)
                                    انتخاب‌ها بر اساس تعداد محصولات ثبت‌شده در سفارش‌های پرداخت‌شده مرتب شده‌اند؛ مشخصات هر مدل را بررسی کنید.
                                @else
                                    مدل‌های منتخب فرزین را ببینید و برای مقایسه مشخصات و انتخاب متناسب با آشپزخانه، وارد صفحه هر محصول شوید.
                                @endif
                            </p>
                        </div>

                        <a class="fz-home-featured-section__all-link fz-text-link" href="{{ route('shop.index') }}">
                            <span>مشاهده همه محصولات</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 12H5m7 7-7-7 7-7"/>
                            </svg>
                        </a>
                    </header>

                    <div class="fz-product-grid fz-home-featured-section__grid {{ match($featuredProducts->count()) { 1 => 'fz-product-grid--single', 2 => 'fz-product-grid--pair', 3 => 'fz-product-grid--triple', default => '' } }}">
                        @foreach($featuredProducts as $product)
                            <x-home.product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($newProducts->isNotEmpty())
            <section class="fz-section fz-new-section" aria-labelledby="fz-new-heading">
                <div class="fz-container">
                    <div class="fz-section-heading fz-reveal">
                        <div>
                            <p class="fz-eyebrow"><span></span> تازه‌های کاتالوگ</p>
                            <h2 id="fz-new-heading">تازه اضافه‌شده‌ها</h2>
                            <p>محصولاتی که در پنل مدیریت به‌عنوان محصول جدید علامت‌گذاری شده‌اند.</p>
                        </div>
                    </div>
                    <div class="fz-product-grid {{ match($newProducts->count()) { 1 => 'fz-product-grid--single', 2 => 'fz-product-grid--pair', default => '' } }}">
                        @foreach($newProducts as $product)
                            <x-home.product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($demoImages->isNotEmpty())
            <section class="fz-mosaic-intro">
                <div class="fz-container fz-mosaic-intro__inner fz-reveal">
                    <p class="fz-eyebrow"><span></span> نگاه نزدیک / گالری محصول</p>
                    <h2>محصولات منتخب فرزین، از نزدیک.</h2>
                    <p>تصاویر واقعی مدل‌های منتخب؛ برای دیدن قیمت و مشخصات هر محصول، تصویر آن را باز کنید.</p>
                    <span class="fz-mosaic-intro__count">{{ \App\Helpers\PersianNumberHelper::format($galleryCount) }} <small>تصویر در گالری</small></span>
                </div>
            </section>

            @foreach($gridGroups as $index => $group)
                @if($group->isNotEmpty())
                    <section class="content {{ $index % 2 ? 'content--spacing' : '' }}" aria-label="تصاویر محصولات فرزین، بخش {{ $index + 1 }}">
                        <div class="fz-container content__heading">
                            <div>
                                <p class="fz-eyebrow"><span></span> FARZIN / PRODUCT DETAIL</p>
                                <h2 class="content__title">جزئیات مدل‌ها را از نزدیک ببینید.</h2>
                            </div>
                            <p class="content__description">برای بررسی قیمت، موجودی و مشخصات کامل، محصول موردنظر را انتخاب کنید.</p>
                        </div>
                        <div class="grid grid--{{ ($index % 6) + 1 }}" data-grid-index="{{ $index }}">
                            <div class="grid-wrap {{ $group->count() <= 2 ? 'grid-wrap--compact' : '' }}">
                                @foreach($group as $item)
                                    <div class="grid__item">
                                        <a
                                            class="grid__item-inner"
                                            href="{{ $item['product_url'] }}"
                                            aria-label="مشاهده {{ $item['product_name'] }}"
                                            data-product-id="{{ $item['product_id'] }}"
                                            data-product-name="{{ $item['product_name'] }}"
                                            data-product-price="{{ $item['product_price'] }}"
                                            data-product-image="{{ $item['url'] }}"
                                            data-product-category="{{ $item['category_name'] }}"
                                            data-product-stock="{{ $item['product_stock'] }}"
                                            data-cart-url="{{ $item['cart_url'] }}"
                                        >
                                            <img class="grid__image" src="{{ $item['url'] }}" @if(!empty($item['srcset'])) srcset="{{ $item['srcset'] }}" sizes="(max-width: 639px) 48vw, (max-width: 1023px) 32vw, 24vw" @endif alt="{{ $item['alt'] }}" width="720" height="900" loading="lazy" decoding="async">
                                            <span class="grid__item-caption"><small>{{ $item['category_name'] }}</small><strong>{{ $item['product_name'] }}</strong></span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif
            @endforeach
        @else
            <section class="fz-section fz-empty-catalog">
                <div class="fz-container fz-empty-state fz-reveal">
                    <span class="fz-empty-state__mark" aria-hidden="true">F</span>
                    <div>
                        <p class="fz-eyebrow"><span></span> کاتالوگ فرزین</p>
                        <h2>{{ $catalogCount > 0 ? 'گالری تصویری در حال تکمیل است.' : 'محصول فعالی برای نمایش منتشر نشده است.' }}</h2>
                        <p>{{ $catalogCount > 0 ? 'محصولات فعال در فهرست کارت‌های پایین نمایش داده می‌شوند. برای حضور در گالری موزاییکی، یک تصویر معتبر برای محصول در پنل ثبت کنید.' : 'از پنل مدیریت، یک محصول ایجاد و وضعیت آن را فعال کنید تا در کاتالوگ عمومی نمایش داده شود.' }}</p>
                        <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M3.5 9h17l-1 11h-15l-1-11Z" /><path d="M8 9V6.5a4 4 0 0 1 8 0V9" /></svg> مشاهده فروشگاه</a>
                    </div>
                </div>
            </section>
        @endif

        <section class="fz-bottom-cta fz-home-purchase-cta" aria-labelledby="fz-home-purchase-heading">
            <div class="fz-container fz-bottom-cta__inner fz-home-purchase-cta__inner fz-reveal">
                <div class="fz-home-purchase-cta__visual" aria-hidden="true">
                    @if($homeCtaImageUrl)
                        <img
                            src="{{ $homeCtaImageUrl }}"
                            @if($homeCtaImageSrcset) srcset="{{ $homeCtaImageSrcset }}" sizes="(max-width: 767px) 100vw, 50vw" @endif
                            alt=""
                            width="1200"
                            height="900"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <span class="fz-home-purchase-cta__fallback">FARZIN</span>
                    @endif
                    <span class="fz-home-purchase-cta__shade"></span>
                    <span class="fz-home-purchase-cta__image-label">FARZIN / KITCHEN EQUIPMENT</span>
                </div>

                <div class="fz-home-purchase-cta__copy">
                    <p class="fz-eyebrow"><span></span> کاتالوگ محصولات فرزین</p>
                    <h2 id="fz-home-purchase-heading">انتخاب مدل مناسب آشپزخانه‌تان را از همین‌جا شروع کنید.</h2>
                    <p>مدل‌های هود و سینک را ببینید، قیمت و مشخصات هر محصول را بررسی کنید و سفارش‌تان را آنلاین ثبت کنید.</p>
                    <a class="fz-home-purchase-cta__button" href="{{ route('shop.index') }}">
                        مشاهده محصولات و قیمت‌ها
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5m7 7-7-7 7-7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>

    <div id="quick-view-modal" class="qv" aria-hidden="true">
        <button class="qv__backdrop" type="button" data-qv-close aria-label="بستن نمای سریع"></button>
        <section class="qv__panel" role="dialog" aria-modal="true" aria-labelledby="qv-name">
            <button class="qv__close unbutton" data-qv-close type="button" aria-label="بستن">×</button>
            <div class="qv__image" data-qv-image></div>
            <div class="qv__body">
                <p class="qv__category" data-qv-category></p>
                <h2 class="qv__name" id="qv-name" data-qv-name></h2>
                <p class="qv__price"><span data-qv-price></span> <small>تومان</small></p>
                <div class="qv__actions"><a class="qv__btn qv__btn--primary" data-qv-link href="#"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg> جزئیات کامل محصول</a><button class="qv__btn qv__btn--ghost" data-qv-add type="button">افزودن به سبد خرید</button></div>
            </div>
        </section>
    </div>
    <div id="toast-root" class="toast-root" aria-live="polite" aria-atomic="true"></div>
@endsection
