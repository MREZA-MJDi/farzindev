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
            $mosaicTitles = [
                ['eyebrow' => 'AIR / 01', 'title' => 'هوای بهتر، شروع یک آشپزخانه‌ی دقیق‌تر.', 'text' => 'فرم، اندازه و جای نصب هود را در کنار فضای پخت‌وپز ببینید.'],
                ['eyebrow' => 'STEEL / 02', 'title' => 'جزئیات کوچک، استفاده‌ی روزمره را می‌سازند.', 'text' => 'در انتخاب سینک، عمق لگن، ابعاد و شیوه‌ی نگهداری را در نظر بگیرید.'],
                ['eyebrow' => 'FORM / 03', 'title' => 'طراحی وقتی خوب است که کار هم بکند.', 'text' => 'ظاهر محصول بخشی از تصمیم است؛ تناسب با کاربری و محل نصب را فراموش نکنید.'],
                ['eyebrow' => 'DETAIL / 04', 'title' => 'از نمای نزدیک، دقیق‌تر انتخاب کنید.', 'text' => 'تصاویر محصولات را ببینید و مشخصات هر مدل را در صفحه‌ی خودش بررسی کنید.'],
                ['eyebrow' => 'FIT / 05', 'title' => 'تناسب با فضا، مهم‌تر از انتخاب عجولانه است.', 'text' => 'اندازه‌ی کابینت، سطح کار و فضای حرکت را پیش از خرید اندازه‌گیری کنید.'],
                ['eyebrow' => 'FARZIN / 06', 'title' => 'تجهیزات آشپزخانه؛ با نگاه کاربردی.', 'text' => 'کاتالوگ فرزین برای شناخت محصولات و پیدا کردن مسیر انتخاب طراحی شده است.'],
            ];

            // Use the already eager-loaded featured catalogue product in the hero.
            // This keeps the first screen product-led without adding another query.
            $heroProduct = $featuredProducts->first(
                fn ($product) => $product->images->contains(fn ($image) => filled($image->url))
            );
            $heroImage = $heroProduct?->images?->first(
                fn ($image) => filled($image->url)
            );
        @endphp

<section class="fz-hero" aria-labelledby="fz-hero-title">
    <div class="fz-hero__stage">
        <div class="fz-hero__backdrop" aria-hidden="true">
            <span class="fz-hero__blueprint-grid"></span>
            <span class="fz-hero__backdrop-shade"></span>
        </div>

        <div class="fz-container fz-hero__layout">
            <div class="fz-hero__copy">
                <p class="fz-eyebrow fz-hero__eyebrow"><span></span> FARZIN / KITCHEN STORE</p>
                <h1 id="fz-hero-title">
                    <span class="fz-hero__brand-name">انتخابی مطمئن برای آشپزخانه شما</span>
                    <span class="fz-hero__brand-claim">هود و سینک فرزین؛ ترکیب زیبایی و کارایی</span>
                </h1>
                <p class="fz-hero__lead">مدل‌های هود و سینک را ببینید، تصاویر و مشخصات را مقایسه کنید و متناسب با فضای آشپزخانه‌تان انتخاب کنید.</p>

                <div class="fz-hero__actions">
                    <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M3.5 9h17l-1 11h-15l-1-11Z" /><path d="M8 9V6.5a4 4 0 0 1 8 0V9" /></svg>
                        مشاهده محصولات
                    </a>
                    <a class="fz-button fz-button--secondary" href="{{ route('contact') }}">مشاوره خرید</a>
                </div>

                <div class="fz-hero__facts" aria-label="مزیت‌های انتخاب فرزین">
                    <div><span class="fz-hero__fact-icon" aria-hidden="true">01</span><span><strong>هود آشپزخانه</strong><small>مدل‌ها و مشخصات</small></span></div>
                    <div><span class="fz-hero__fact-icon" aria-hidden="true">02</span><span><strong>سینک ظرف‌شویی</strong><small>جنس، ابعاد و کاربری</small></span></div>
                    <div><span class="fz-hero__fact-icon" aria-hidden="true">03</span><span><strong>راهنمای انتخاب</strong><small>پیش از ثبت سفارش</small></span></div>
                </div>
            </div>

            <div class="fz-hero__visual">
                @if($heroProduct && $heroImage)
                    <a href="{{ route('product.show', $heroProduct->slug) }}" class="fz-hero-product" aria-label="مشاهده {{ $heroProduct->name }}">
                        <span class="fz-hero-product__media">
                            <img
                                src="{{ $heroImage->url }}"
                                @if($heroImage->responsive_srcset) srcset="{{ $heroImage->responsive_srcset }}" sizes="(max-width: 767px) 92vw, 48vw" @endif
                                alt="{{ $heroImage->alt ?: $heroProduct->name }}"
                                width="1000"
                                height="820"
                                fetchpriority="high"
                                loading="eager"
                                decoding="async"
                            >
                            <span class="fz-hero-product__badge">پیشنهاد فرزین</span>
                        </span>
                        <span class="fz-hero-product__details">
                            <span class="fz-hero-product__category">{{ $heroProduct->category?->name ?? 'محصول فرزین' }}</span>
                            <strong>{{ $heroProduct->name }}</strong>
                            <span class="fz-hero-product__footer">
                                <span class="fz-hero-product__price">{{ \App\Helpers\PersianNumberHelper::format((float) $heroProduct->price) }} <small>تومان</small></span>
                                <span class="fz-hero-product__link">مشاهده جزئیات <b aria-hidden="true">←</b></span>
                            </span>
                        </span>
                    </a>
                @else
                    <div class="fz-hero-product fz-hero-product--fallback" aria-label="کاتالوگ محصولات فرزین">
                        <span class="fz-hero-product__fallback-mark" aria-hidden="true">FZ</span>
                        <strong>تجهیزات آشپزخانه فرزین</strong>
                        <span>هود و سینک؛ مدل‌ها، تصاویر و مشخصات</span>
                        <a href="{{ route('shop.index') }}" class="fz-hero-product__link">مشاهده کاتالوگ <b aria-hidden="true">←</b></a>
                    </div>
                @endif
            </div>
        </div>

        <div class="fz-hero__bottom">
            <span>FARZIN / KITCHEN EQUIPMENT</span>
            <span>هود · سینک · انتخاب آگاهانه</span>
            <a class="fz-hero__scroll-cue" href="#product-categories">
                <span>دسته‌بندی محصولات</span>
                <b aria-hidden="true">↓</b>
            </a>
        </div>
    </div>
</section>

        @if($categories->isNotEmpty())
            <section id="product-categories" class="fz-section fz-category-section" aria-labelledby="fz-category-heading">
                <div class="fz-container">
                    <div class="fz-section-heading fz-reveal">
                        <div>
                            <p class="fz-eyebrow"><span></span> مسیر انتخاب</p>
                            <h2 id="fz-category-heading">از دسته‌ی مناسب شروع کنید.</h2>
                            <p>برای رسیدن سریع‌تر به محصول موردنیاز، دسته‌بندی را انتخاب کنید.</p>
                        </div>
                        <a class="fz-text-link" href="{{ route('categories.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.3" /><rect x="13.5" y="3.5" width="7" height="7" rx="1.3" /><rect x="3.5" y="13.5" width="7" height="7" rx="1.3" /><rect x="13.5" y="13.5" width="7" height="7" rx="1.3" /></svg> تمام دسته‌ها</a>
                    </div>
                    <div class="fz-category-grid">
                        @foreach($categories as $category)
                            <a class="fz-category-card fz-reveal" href="{{ route('categories.show', $category) }}" @if($category->hoverMedia?->url) data-farzin-gif-card @endif>
                                <span class="fz-category-card__art">
                                    @if($category->homepage_image_url)
                                        <img class="fz-category-card__image" src="{{ $category->homepage_image_url }}" @if($category->homepage_image_srcset) srcset="{{ $category->homepage_image_srcset }}" sizes="(max-width: 639px) 85vw, (max-width: 1023px) 45vw, 320px" @endif alt="" width="640" height="480" loading="lazy" decoding="async">
                                    @else
                                        <span class="fz-category-card__monogram">{{ $category->slug === 'sinks' ? 'S' : 'H' }}</span>
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
                                    <span class="fz-category-card__arrow" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg></span>
                                </span>
                                <span class="fz-category-card__content"><strong>{{ $category->name }}</strong><small>{{ \App\Helpers\PersianNumberHelper::format($category->products_count) }} محصول فعال</small></span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($demoImages->isNotEmpty())
            <section class="fz-mosaic-intro">
                <div class="fz-container fz-mosaic-intro__inner fz-reveal">
                    <p class="fz-eyebrow"><span></span> نگاه نزدیک / گالری محصول</p>
                    <h2>از نمای کلی تا جزئیات محصول.</h2>
                    <p>تصاویر زیر از محصولات منتشرشده در پنل مدیریت می‌آیند. روی هر تصویر بزنید تا اطلاعات همان محصول را ببینید.</p>
                    <span class="fz-mosaic-intro__count">{{ \App\Helpers\PersianNumberHelper::format($galleryCount) }} <small>تصویر در گالری</small></span>
                </div>
            </section>

            @foreach($gridGroups as $index => $group)
                @if($group->isNotEmpty())
                    <section class="content {{ $index % 2 ? 'content--spacing' : '' }}" aria-label="تصاویر محصولات فرزین، بخش {{ $index + 1 }}">
                        <div class="fz-container content__heading">
                            <div>
                                <p class="fz-eyebrow"><span></span> {{ $mosaicTitles[$index % count($mosaicTitles)]['eyebrow'] }}</p>
                                <h2 class="content__title">{{ $mosaicTitles[$index % count($mosaicTitles)]['title'] }}</h2>
                            </div>
                            <p class="content__description">{{ $mosaicTitles[$index % count($mosaicTitles)]['text'] }}</p>
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

        @if($featuredProducts->isNotEmpty())
            <section class="fz-section fz-product-section" aria-labelledby="fz-products-heading">
                <div class="fz-container">
                    <div class="fz-section-heading fz-reveal">
                        <div>
                            <p class="fz-eyebrow"><span></span> کاتالوگ محصولات</p>
                            <h2 id="fz-products-heading">{{ $hasBestSellerSales ? 'پرفروش‌ترین محصولات' : 'مدل‌های منتخب فرزین' }}</h2>
                            <p>
                                @if($hasBestSellerSales)
                                    بر اساس تعداد محصولات ثبت‌شده در سفارش‌های پرداخت‌شده؛ برای مشخصات کامل، صفحه‌ی هر محصول را باز کنید.
                                @else
                                    سفارش پرداخت‌شده‌ای برای رتبه‌بندی ثبت نشده است؛ مدل‌های منتخب فرزین را بررسی کنید.
                                @endif
                            </p>
                        </div>
                        <a class="fz-text-link" href="{{ route('shop.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M3.5 9h17l-1 11h-15l-1-11Z" /><path d="M8 9V6.5a4 4 0 0 1 8 0V9" /></svg> مشاهده همه محصولات</a>
                    </div>
                    <div class="fz-product-grid {{ match($featuredProducts->count()) { 1 => 'fz-product-grid--single', 2 => 'fz-product-grid--pair', 3 => 'fz-product-grid--triple', default => '' } }}">
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
                        <a class="fz-text-link" href="{{ route('shop.index', ['sort' => 'newest']) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg> جدیدترین‌ها</a>
                    </div>
                    <div class="fz-product-grid {{ match($newProducts->count()) { 1 => 'fz-product-grid--single', 2 => 'fz-product-grid--pair', default => '' } }}">
                        @foreach($newProducts as $product)
                            <x-home.product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="fz-maker" aria-labelledby="fz-maker-heading">
            <div class="fz-container fz-maker__layout">
                <div class="fz-maker__copy fz-reveal">
                    <p class="fz-eyebrow fz-eyebrow--light"><span></span> فرزین / تجهیزات آشپزخانه</p>
                    <h2 id="fz-maker-heading">محصول کاربردی، از تصمیم‌های دقیق ساخته می‌شود.</h2>
                    <p>انتخاب هود و سینک فقط به رنگ و فرم محدود نیست. هماهنگی با کابینت، فضای نصب، نظافت و نیاز روزانه بخشی از تجربه‌ی بهتر آشپزخانه‌اند.</p>
                    <a class="fz-button fz-button--light" href="{{ route('about') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 11v5m0-8h.01" /></svg> درباره مجموعه</a>
                </div>
                <div class="fz-maker__specs">
                    <article class="fz-maker__spec fz-reveal"><span>01 / SPACE</span><h3>تناسب با فضا</h3><p>ابعاد و جای نصب را با محصول نهایی تطبیق دهید.</p></article>
                    <article class="fz-maker__spec fz-reveal"><span>02 / MATERIAL</span><h3>جنس و نگهداری</h3><p>روش نظافت و شرایط استفاده را در تصمیم‌گیری دخیل کنید.</p></article>
                    <article class="fz-maker__spec fz-reveal"><span>03 / FUNCTION</span><h3>کارایی روزمره</h3><p>مشخصات فنی و کارکردهای محصول را قبل از انتخاب بخوانید.</p></article>
                </div>
            </div>
            <div class="fz-maker__watermark" aria-hidden="true">FZ</div>
        </section>

        <section class="fz-bottom-cta">
            <div class="fz-container fz-bottom-cta__inner fz-reveal">
                <div>
                    <p class="fz-eyebrow"><span></span> گام بعدی</p>
                    <h2>مدل متناسب با آشپزخانه‌تان را پیدا کنید.</h2>
                    <p>کاتالوگ را ببینید یا برای دریافت راهنمایی، با فرزین در تماس باشید.</p>
                </div>
                <div class="fz-hero__actions">
                    <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M3.5 9h17l-1 11h-15l-1-11Z" /><path d="M8 9V6.5a4 4 0 0 1 8 0V9" /></svg> ورود به فروشگاه</a>
                    <a class="fz-button fz-button--secondary" href="{{ route('contact') }}">تماس با فرزین</a>
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
