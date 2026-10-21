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
        @endphp

        <section class="fz-hero" aria-labelledby="fz-hero-title">
            <div class="fz-container fz-hero__layout">
                <div class="fz-hero__copy fz-reveal">
                    <p class="fz-eyebrow"><span></span> فرزین / تولید و تجهیزات آشپزخانه</p>
                    <h1 id="fz-hero-title">کارایی از جزئیات شروع می‌شود؛ <span>هود و سینک فرزین.</span></h1>
                    <p class="fz-hero__lead">محصول را فقط از روی ظاهر انتخاب نکنید. فرم، فضای نصب و شیوه‌ی استفاده را کنار هم ببینید تا انتخاب نهایی با آشپزخانه‌ی شما هماهنگ باشد.</p>
                    <div class="fz-hero__actions">
                        <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}">دیدن محصولات <span aria-hidden="true">←</span></a>
                        <a class="fz-button fz-button--secondary" href="{{ route('about') }}">آشنایی با فرزین</a>
                    </div>
                    <div class="fz-hero__facts">
                        <div><span class="fz-hero__fact-icon">01</span><span><strong>هود آشپزخانه</strong><small>فرم و تهویه</small></span></div>
                        <div><span class="fz-hero__fact-icon">02</span><span><strong>سینک ظرفشویی</strong><small>کاربری و تناسب</small></span></div>
                        <div><span class="fz-hero__fact-icon">03</span><span><strong>انتخاب دقیق</strong><small>جزئیات قابل بررسی</small></span></div>
                    </div>
                </div>

                <div class="fz-hero__visual fz-reveal fz-reveal--delay">
                    @if($heroProduct && $heroProduct->images->first(fn ($image) => filled($image->url)))
                        @php($heroImage = $heroProduct->images->first(fn ($image) => filled($image->url)))
                        <a class="fz-hero-product" href="{{ route('product.show', $heroProduct->slug) }}" aria-label="مشاهده {{ $heroProduct->name }}">
                            <img src="{{ $heroImage->url }}" alt="{{ $heroImage->alt ?: $heroProduct->name }}" width="1200" height="1000" loading="eager" fetchpriority="high" decoding="async">
                            <span class="fz-hero-product__veil" aria-hidden="true"></span>
                            <span class="fz-hero-product__index">FZ / PRODUCT {{ str_pad((string) $heroProduct->id, 3, '0', STR_PAD_LEFT) }}</span>
                            <span class="fz-hero-product__meta">
                                <span>در کاتالوگ فرزین</span>
                                <strong>{{ $heroProduct->name }}</strong>
                                <span class="fz-hero-product__link">مشاهده مشخصات <b aria-hidden="true">↗</b></span>
                            </span>
                        </a>
                    @else
                        <div class="fz-hero-empty" role="img" aria-label="تصویر محصول پس از ثبت تصویر معتبر در پنل مدیریت نمایش داده می‌شود">
                            <span class="fz-hero-empty__label">FARZIN / KITCHEN SYSTEMS</span>
                            <div class="fz-hero-empty__orb fz-hero-empty__orb--one"></div>
                            <div class="fz-hero-empty__orb fz-hero-empty__orb--two"></div>
                            <svg class="fz-hero-empty__drawing" viewBox="0 0 420 300" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M65 104L210 45L355 104" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M98 106H322L296 151H124L98 106Z" fill="rgba(255,255,255,.72)" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                <path d="M147 151V177H273V151" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                <path d="M76 205H344V256H76V205Z" fill="rgba(255,255,255,.4)" stroke="currentColor" stroke-width="3"/>
                                <path d="M100 220H320M100 233H320M100 246H320" stroke="currentColor" stroke-width="1.5" opacity=".5"/>
                                <path d="M154 205V189H266V205" stroke="currentColor" stroke-width="3"/>
                                <path d="M190 189V178C190 168 199 160 209 160H211C221 160 230 168 230 178V189" stroke="currentColor" stroke-width="3"/>
                                <circle cx="210" cy="83" r="4" fill="currentColor"/>
                                <circle cx="194" cy="83" r="2.5" fill="currentColor" opacity=".5"/>
                                <circle cx="226" cy="83" r="2.5" fill="currentColor" opacity=".5"/>
                            </svg>
                            <strong>تجهیزات آشپزخانه، با نگاه دقیق</strong>
                            <span>تصویر اصلی را از پنل مدیریت ثبت کنید.</span>
                        </div>
                    @endif
                    <span class="fz-hero__edge-note" aria-hidden="true">FORM FOLLOWS FUNCTION · 2026</span>
                </div>
            </div>
            <div class="fz-hero__bottom"><span>FARZIN</span><span>HOODS / SINKS / KITCHEN</span><span>01 — 06</span></div>
        </section>

        @if($categories->isNotEmpty())
            <section class="fz-section fz-category-section" aria-labelledby="fz-category-heading">
                <div class="fz-container">
                    <div class="fz-section-heading fz-reveal">
                        <div>
                            <p class="fz-eyebrow"><span></span> مسیر انتخاب</p>
                            <h2 id="fz-category-heading">از دسته‌ی مناسب شروع کنید.</h2>
                            <p>برای رسیدن سریع‌تر به محصول موردنیاز، دسته‌بندی را انتخاب کنید.</p>
                        </div>
                        <a class="fz-text-link" href="{{ route('categories.index') }}">تمام دسته‌ها <span aria-hidden="true">←</span></a>
                    </div>
                    <div class="fz-category-grid">
                        @foreach($categories as $category)
                            <a class="fz-category-card fz-reveal" href="{{ route('categories.show', $category) }}">
                                <span class="fz-category-card__art">
                                    @if($category->homepage_image_url)
                                        <img src="{{ $category->homepage_image_url }}" alt="" width="640" height="480" loading="lazy" decoding="async">
                                    @else
                                        <span class="fz-category-card__monogram">{{ $category->slug === 'sinks' ? 'S' : 'H' }}</span>
                                    @endif
                                    <span class="fz-category-card__arrow" aria-hidden="true">↗</span>
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
                            <div class="grid-wrap">
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
                                            <img class="grid__image" src="{{ $item['url'] }}" alt="{{ $item['alt'] }}" width="720" height="900" loading="lazy" decoding="async">
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
                        <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}">مشاهده فروشگاه <span aria-hidden="true">←</span></a>
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
                            <h2 id="fz-products-heading">محصولات فرزین</h2>
                            <p>{{ \App\Helpers\PersianNumberHelper::format($catalogCount) }} محصول فعال در کاتالوگ ثبت شده است؛ برای مشخصات کامل، صفحه‌ی همان محصول را باز کنید.</p>
                        </div>
                        <a class="fz-text-link" href="{{ route('shop.index') }}">دیدن همه‌ی محصولات <span aria-hidden="true">←</span></a>
                    </div>
                    <div class="fz-product-grid">
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
                        <a class="fz-text-link" href="{{ route('shop.index', ['sort' => 'newest']) }}">جدیدترین‌ها <span aria-hidden="true">←</span></a>
                    </div>
                    <div class="fz-product-grid">
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
                    <a class="fz-button fz-button--light" href="{{ route('about') }}">درباره مجموعه <span aria-hidden="true">←</span></a>
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
                    <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}">ورود به فروشگاه <span aria-hidden="true">←</span></a>
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
                <div class="qv__actions"><a class="qv__btn qv__btn--primary" data-qv-link href="#">جزئیات کامل محصول <span aria-hidden="true">↗</span></a><button class="qv__btn qv__btn--ghost" data-qv-add type="button">افزودن به سبد خرید</button></div>
            </div>
        </section>
    </div>
    <div id="toast-root" class="toast-root" aria-live="polite" aria-atomic="true"></div>
@endsection
