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

<section class="fz-hero fz-hero--scroll" data-fz-scroll-hero aria-labelledby="fz-hero-title">
    <div class="fz-hero__stage" data-hero-stage>
        <div class="fz-hero__backdrop" aria-hidden="true">
            <span class="fz-hero__blueprint-grid"></span>
            <span class="fz-hero__backdrop-shade"></span>
        </div>

        <div class="fz-container fz-hero__layout">
            <div class="fz-hero__copy">
                <p class="fz-eyebrow fz-hero__eyebrow"><span></span> FARZIN / ARCHITECTURAL ENGINEERING</p>
                <h1 id="fz-hero-title">
                    <span class="fz-hero__brand-name">فرزین</span>
                    <span class="fz-hero__brand-claim">تولید تخصصی هود و سینک آشپزخانه</span>
                </h1>
                <p class="fz-hero__lead">از طراحی تا تولید؛ تجهیزاتی برای آشپزخانه‌های امروزی، با تمرکز بر فرم، کارکرد و جزئیات ساخت.</p>
                <div class="fz-hero__actions">
                    <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M3.5 9h17l-1 11h-15l-1-11Z" /><path d="M8 9V6.5a4 4 0 0 1 8 0V9" /></svg> مشاهده محصولات</a>
                    <a class="fz-button fz-button--secondary" href="{{ route('about') }}">آشنایی با تولید فرزین</a>
                </div>
                <div class="fz-hero__facts" aria-label="زمینه فعالیت فرزین">
                    <div><span class="fz-hero__fact-icon" aria-hidden="true">01</span><span><strong>هود آشپزخانه</strong><small>طراحی و تهویه</small></span></div>
                    <div><span class="fz-hero__fact-icon" aria-hidden="true">02</span><span><strong>سینک ظرفشویی</strong><small>فرم و کاربری</small></span></div>
                    <div><span class="fz-hero__fact-icon" aria-hidden="true">03</span><span><strong>تولید تخصصی</strong><small>از کارخانه تا خانه</small></span></div>
                </div>
            </div>

            <div class="fz-hero__visual" aria-hidden="true">
                <div class="fz-hero__art-meta">
                    <span>FZ / TECHNICAL STUDY 01</span>
                    <span data-hero-progress>00 / 100</span>
                </div>
                <span class="fz-hero__visual-orbit"></span>
                <svg class="fz-hero__architecture" data-hero-drawing viewBox="0 0 860 620" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="fz-hero-grid-pattern" width="28" height="28" patternUnits="userSpaceOnUse">
                            <path d="M28 0H0V28" stroke="#FFFFFF" stroke-width=".55" opacity=".16"/>
                        </pattern>
                        <linearGradient id="fz-hero-metal" x1="120" y1="130" x2="740" y2="500" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FFFFFF" stop-opacity=".9"/>
                            <stop offset=".52" stop-color="#BFC3C8" stop-opacity=".35"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".8"/>
                        </linearGradient>
                    </defs>
                    <path d="M20 90H840V600H20Z" fill="url(#fz-hero-grid-pattern)" opacity=".48"/>
                    <g class="fz-hero__blueprint-base" fill="none" stroke="#E5E7EB" stroke-width="1.15" stroke-linejoin="round" stroke-linecap="round">
                        <path d="M58 171 399 68 819 173 473 291Z"/>
                        <path d="M58 171V402L473 568V291"/>
                        <path d="M473 291V568L819 431V173"/>
                        <path d="M82 181 398 86 793 184"/>
                        <path d="M100 201 394 112 769 205"/>
                        <path d="M100 201V274L394 187V112"/>
                        <path d="M414 114 748 207V274L414 197Z"/>
                        <path d="M119 208 193 186V249L119 271Z M207 181 279 160V223L207 244Z M293 155 365 134V198L293 219Z"/>
                        <path d="M431 128 495 146V205L431 188Z M508 150 572 168V225L508 208Z M585 171 649 189V246L585 229Z M662 193 724 210V266L662 248Z"/>
                        <path d="M101 294 402 204 769 291 470 394Z"/>
                        <path d="M101 294V329L470 430V394"/>
                        <path d="M470 394 769 291V326L470 430"/>
                        <path d="M112 339 212 366V463L112 423Z M225 370 312 394V505L225 471Z M325 398 411 421V544L325 510Z"/>
                        <path d="M493 421 577 393V491L493 523Z M591 388 678 358V460L591 493Z M691 354 758 331V412L691 436Z"/>
                        <path d="M243 391 464 325 688 383 438 463Z"/>
                        <path d="M243 391V487L438 569V463"/>
                        <path d="M438 463 688 383V476L438 569"/>
                        <path d="M272 403 439 350 654 391"/>
                        <path d="M272 430 420 479V541 M292 437 292 511 M317 446 317 522 M344 453 344 533"/>
                        <path d="M474 466 650 409V457L474 516Z"/>
                        <path d="M490 477 490 510 M508 472 508 504 M526 466 526 498"/>
                        <path d="M110 451 205 488 M100 475 193 512 M88 500 179 536 M77 525 164 558 M67 551 150 582"/>
                        <path d="M520 555 790 448 M540 579 813 470 M569 599 835 494"/>
                    </g>
                    <g fill="none" stroke="url(#fz-hero-metal)" stroke-width="1.45" stroke-linecap="round" stroke-linejoin="round">
                        <g data-hero-draw data-hero-phase="0">
                            <path pathLength="1" d="M58 171 399 68 819 173 473 291Z"/>
                            <path pathLength="1" d="M58 171V402L473 568V291"/>
                            <path pathLength="1" d="M473 291V568L819 431V173"/>
                            <path pathLength="1" d="M82 181 398 86 793 184"/>
                            <path pathLength="1" d="M100 201 394 112 769 205"/>
                        </g>
                        <g data-hero-draw data-hero-phase="1">
                            <path pathLength="1" d="M100 201V274L394 187V112"/>
                            <path pathLength="1" d="M414 114 748 207V274L414 197Z"/>
                            <path pathLength="1" d="M119 208 193 186V249L119 271Z M207 181 279 160V223L207 244Z M293 155 365 134V198L293 219Z"/>
                            <path pathLength="1" d="M431 128 495 146V205L431 188Z M508 150 572 168V225L508 208Z M585 171 649 189V246L585 229Z M662 193 724 210V266L662 248Z"/>
                            <path pathLength="1" d="M312 229 397 204 489 229 404 255Z M335 235 398 216 466 235 403 253Z M360 238V267L403 279V253 M446 229V258L403 279"/>
                        </g>
                        <g data-hero-draw data-hero-phase="2">
                            <path pathLength="1" d="M101 294 402 204 769 291 470 394Z"/>
                            <path pathLength="1" d="M101 294V329L470 430V394 M470 394 769 291V326L470 430"/>
                            <path pathLength="1" d="M153 286 193 274 M176 293 216 281 M199 300 239 288 M222 307 262 295 M245 314 285 302 M268 321 308 309 M291 328 331 316 M314 335 354 323 M337 342 377 330 M360 349 400 337 M383 356 423 344 M406 363 446 351 M429 370 469 358"/>
                            <path pathLength="1" d="M531 309 620 331 672 316 583 293Z M545 310 590 322 M611 314 652 323"/>
                            <path pathLength="1" d="M144 308 198 323 234 312 179 297Z M160 307V322 M181 304V328 M202 299V334"/>
                        </g>
                        <g data-hero-draw data-hero-phase="3">
                            <path pathLength="1" d="M112 339 212 366V463L112 423Z M225 370 312 394V505L225 471Z M325 398 411 421V544L325 510Z"/>
                            <path pathLength="1" d="M493 421 577 393V491L493 523Z M591 388 678 358V460L591 493Z M691 354 758 331V412L691 436Z"/>
                            <path pathLength="1" d="M243 391 464 325 688 383 438 463Z M243 391V487L438 569V463 M438 463 688 383V476L438 569"/>
                            <path pathLength="1" d="M272 403 439 350 654 391 M272 430 420 479V541 M292 437 292 511 M317 446 317 522 M344 453 344 533"/>
                            <path pathLength="1" d="M474 466 650 409V457L474 516Z M490 477 490 510 M508 472 508 504 M526 466 526 498"/>
                        </g>
                        <g data-hero-draw data-hero-phase="4">
                            <path pathLength="1" d="M58 143 400 41 821 146 M58 139V160 M400 37V57 M821 142V164"/>
                            <path pathLength="1" d="M835 174V431 M827 174H842 M827 431H842"/>
                            <path pathLength="1" d="M244 377 464 311 700 373 M239 372 239 389 M465 306V326 M700 368V386"/>
                            <path pathLength="1" d="M385 95l12 -4 12 4 M397 91V111 M390 111H404"/>
                            <path pathLength="1" d="M116 278 91 286 M119 284 94 292 M752 282 782 273 M752 289 782 280"/>
                            <path pathLength="1" d="M131 430h21 M131 438h21 M131 446h21 M247 399h20 M247 407h20 M247 415h20 M607 414h25 M607 422h25"/>
                        </g>
                    </g>
                    <g fill="#F7F7F4" opacity=".8">
                        <circle cx="58" cy="171" r="2.5"/><circle cx="399" cy="68" r="2.5"/><circle cx="819" cy="173" r="2.5"/>
                        <circle cx="473" cy="291" r="2.5"/><circle cx="438" cy="569" r="2.5"/>
                    </g>
                </svg>
                <div class="fz-hero__art-caption">
                    <span>FORM / 01</span>
                    <span>HOOD · SINK · SPATIAL DETAIL</span>
                </div>
            </div>
        </div>

        <div class="fz-hero__bottom">
            <span>FARZIN / MANUFACTURING</span>
            <span>HOODS · SINKS · KITCHEN SYSTEMS</span>
            <span class="fz-hero__scroll-cue"><span>SCROLL TO REVEAL</span><b aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="5" /><path d="M12 7v3" /></svg></b></span>
        </div>
    </div>
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
