@extends('layouts.app')

@section('title', 'فرزین | تولید هود و سینک آشپزخانه')
@section('description', 'آشنایی با فرزین و تجهیزات آشپزخانه؛ هود و سینک را ببینید، مشخصات محصولات را مقایسه کنید و برای انتخابی متناسب با فضای نصب راهنمایی بگیرید.')
@section('canonical', route('home'))
@section('body_class', 'home-body min-h-screen antialiased')

@section('content')
    <a class="fz-skip-link" href="#main-content">رفتن به محتوای اصلی</a>

@php
    $homeCtaCategory = $categories->first(
        fn ($category) => filled($category->homepage_image_url)
    );

    $homeCtaProduct = $featuredProducts->first() ?? $heroProduct;
    $homeCtaProductImage = $homeCtaProduct?->images->first(
        fn ($image) => filled($image->url)
    );

    $homeCtaImageUrl = $homeCtaCategory?->homepage_image_url
        ?? $homeCtaProductImage?->url;
    $homeCtaImageSrcset = $homeCtaCategory?->homepage_image_srcset
        ?? $homeCtaProductImage?->responsive_srcset;

    $heroImage = $heroProduct?->images->first(
        fn ($image) => filled($image->url)
    );
    $hoodCategory = $categories->firstWhere('slug', 'hoods');
    $sinkCategory = $categories->firstWhere('slug', 'sinks');
@endphp

<section class="fz-home-hero" aria-labelledby="fz-home-hero-title">
    <div class="fz-home-hero__ambient" aria-hidden="true">
        <span class="fz-home-hero__ambient-orb fz-home-hero__ambient-orb--one"></span>
        <span class="fz-home-hero__ambient-orb fz-home-hero__ambient-orb--two"></span>
        <span class="fz-home-hero__ambient-grid"></span>
    </div>
    <div class="fz-container fz-home-hero__layout">
        <div class="fz-home-hero__copy">
            <p class="fz-home-hero__eyebrow"><span class="fz-home-hero__eyebrow-mark" aria-hidden="true"></span> تجهیزات آشپزخانه فرزین</p>
            <h1 id="fz-home-hero-title"><span>تولید تخصصی</span><strong>هود و سینک آشپزخانه</strong></h1>
            <p class="fz-home-hero__lead">مدل‌های هود و سینک فرزین را بررسی کنید؛ ابعاد، مشخصات و قیمت هر محصول را با فضای آشپزخانه‌تان تطبیق دهید.</p>
            <div class="fz-home-hero__actions">
                <a class="fz-home-hero__button fz-home-hero__button--primary" href="{{ $hoodCategory ? route('categories.show', $hoodCategory) : route('shop.index') }}">مشاهده هودها</a>
                <a class="fz-home-hero__button fz-home-hero__button--secondary" href="{{ route('contact') }}">درخواست راهنمایی</a>
            </div>
            @php
                $homeServiceHighlights = collect([
                    ['label' => 'گارانتی', 'value' => data_get($storefrontContact ?? [], 'warranty_summary')],
                    ['label' => 'ارسال', 'value' => data_get($storefrontContact ?? [], 'shipping_summary')],
                    ['label' => 'نصب', 'value' => data_get($storefrontContact ?? [], 'installation_summary')],
                ])->filter(fn ($item) => filled($item['value']));
                $homeInstallmentAvailable = $featuredProducts->contains(fn ($product) => (bool) $product->installment_enabled)
                    || (bool) ($heroProduct?->installment_enabled);
            @endphp
            @if($homeServiceHighlights->isNotEmpty() || $homeInstallmentAvailable)
                <div class="fz-home-hero__highlights" aria-label="خدمات ثبت‌شده فرزین">
                    @foreach($homeServiceHighlights as $service)
                        <span><b aria-hidden="true">✓</b> {{ $service['label'] }}: {{ $service['value'] }}</span>
                    @endforeach
                    @if($homeInstallmentAvailable)
                        <span><b aria-hidden="true">✓</b> امکان خرید اقساطی برای محصولات منتخب</span>
                    @endif
                </div>
            @endif
        </div>
        @if($heroProduct && $heroImage)
            <a class="fz-home-hero__showcase fz-home-hero__showcase--static" href="{{ route('product.show', $heroProduct->slug) }}" aria-label="مشاهده {{ $heroProduct->name }}">
                <span class="fz-home-hero__showcase-heading">
                    <span><strong>پیشنهاد فرزین</strong><small>{{ $heroProduct->category?->name ?? 'محصول منتخب' }}</small></span>
                    <span class="fz-home-hero__showcase-label">محصول منتخب</span>
                </span>
                <span class="fz-home-hero__product-image">
                    <img src="{{ $heroImage->url }}" @if($heroImage->responsive_srcset) srcset="{{ $heroImage->responsive_srcset }}" sizes="(max-width: 767px) 92vw, 48vw" @endif alt="{{ $heroImage->alt ?: $heroProduct->name }}" width="960" height="720" fetchpriority="high" loading="eager" decoding="async">
                </span>
                <span class="fz-home-hero__product-details">
                    <strong>{{ $heroProduct->name }}</strong>
                    <span class="fz-home-hero__product-footer">
                        <span class="fz-home-hero__product-price">{{ \App\Helpers\PersianNumberHelper::format((float) $heroProduct->price) }} <small>تومان</small></span>
                        <span class="fz-home-hero__product-link">بررسی مشخصات</span>
                    </span>
                </span>
            </a>
        @else
            <div class="fz-home-hero__showcase fz-home-hero__showcase--empty">
                <span class="fz-home-hero__empty-mark" aria-hidden="true">FZ</span>
                <div><strong>کاتالوگ محصولات فرزین</strong><p>مدل‌های هود و سینک را در فروشگاه ببینید.</p><a href="{{ route('shop.index') }}">مشاهده کاتالوگ</a></div>
            </div>
        @endif
    </div>
    <div class="fz-container fz-home-hero__bottom">
        <span>تولید هود و سینک آشپزخانه</span><span>هود آشپزخانه · سینک ظرف‌شویی</span><a href="#product-categories">شروع انتخاب</a>
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
            <section class="fz-section fz-product-section fz-home-featured-section" aria-labelledby="fz-products-heading">
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

                    <div class="fz-product-grid fz-home-featured-section__grid fz-home-featured-grid--six">
                        @foreach($featuredProducts as $product)
                            <x-product.card :product="$product" variant="round" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="fz-home-trust" aria-labelledby="fz-home-trust-heading">
            <div class="fz-container">
                <div class="fz-home-trust__intro">
                    <p class="fz-eyebrow"><span></span> تجربه خرید از فرزین</p>
                    <h2 id="fz-home-trust-heading">برای انتخاب، اطلاعات روشن در اختیار شماست.</h2>
                </div>
                <div class="fz-home-trust__grid">
                    <a class="fz-home-trust__item" href="{{ route('shop.index') }}">
                        <span class="fz-home-trust__index">01</span><span class="fz-home-trust__icon" aria-hidden="true">◇</span>
                        <strong>مشخصات پیش از خرید</strong><span>تصویر، قیمت و جزئیات هر مدل را در صفحه محصول بررسی کنید.</span>
                        <em>رفتن به فروشگاه <b aria-hidden="true">←</b></em>
                    </a>
                    <a class="fz-home-trust__item" href="{{ route('cart.index') }}">
                        <span class="fz-home-trust__index">02</span><span class="fz-home-trust__icon" aria-hidden="true">＋</span>
                        <strong>سبد خرید یکپارچه</strong><span>محصول‌ها را به سبد اضافه کنید؛ سبد مهمان پس از ورود به حساب منتقل می‌شود.</span>
                        <em>مشاهده سبد خرید <b aria-hidden="true">←</b></em>
                    </a>
                    <a class="fz-home-trust__item" href="{{ auth()->check() && auth()->user()->isCustomer() ? route('account.orders.index') : route('login', ['intended' => '/account/orders']) }}">
                        <span class="fz-home-trust__index">03</span><span class="fz-home-trust__icon" aria-hidden="true">↗</span>
                        <strong>مدیریت سفارش‌ها</strong><span>پس از ورود، سفارش‌های ثبت‌شده را از حساب کاربری خود ببینید.</span>
                        <em>حساب کاربری <b aria-hidden="true">←</b></em>
                    </a>
                </div>
            </div>
        </section>

        <section class="fz-section fz-home-buying-guides" aria-labelledby="fz-buying-guides-title">
            <div class="fz-container">
                <header class="fz-home-buying-guides__heading">
                    <div>
                        <p class="fz-eyebrow"><span></span> راهنمای شروع انتخاب</p>
                        <h2 id="fz-buying-guides-title">از نیاز آشپزخانه‌تان شروع کنید.</h2>
                        <p>مسیر مناسب را انتخاب کنید تا مدل‌ها و مشخصات مرتبط را ببینید.</p>
                    </div>
                </header>
                <div class="fz-home-buying-guides__grid">
                    <a class="fz-home-buying-guide" href="{{ $hoodCategory ? route('categories.show', $hoodCategory) : route('categories.index') }}">
                        <span class="fz-home-buying-guide__index">۰۱</span>
                        <span><strong>راهنمای انتخاب هود</strong><small>نوع نصب، عرض و مشخصات مدل‌ها را بررسی کنید.</small></span>
                        <span class="fz-home-buying-guide__action">مشاهده هودها</span>
                    </a>
                    <a class="fz-home-buying-guide" href="{{ $sinkCategory ? route('categories.show', $sinkCategory) : route('categories.index') }}">
                        <span class="fz-home-buying-guide__index">۰۲</span>
                        <span><strong>راهنمای انتخاب سینک</strong><small>جنس، تعداد لگن و ابعاد مناسب را مقایسه کنید.</small></span>
                        <span class="fz-home-buying-guide__action">مشاهده سینک‌ها</span>
                    </a>
                </div>
            </div>
        </section>

        @if($blogPosts->count() >= 3)
            @php
                $homeFeaturedArticle = $blogPosts->first();
                $homeJournalStories = $blogPosts->skip(1);
                $homeFeaturedCategory = $homeFeaturedArticle->terms->firstWhere('type', 'category');
                $homeFeaturedArticleImage = $homeFeaturedArticle->cover_image_url;
                $homeFeaturedArticleImageAlt = $homeFeaturedArticle->cover_image_alt ?: $homeFeaturedArticle->title;
            @endphp

            <section class="fz-home-journal" aria-labelledby="fz-home-journal-heading">
                <div class="fz-container fz-home-journal__container">
                    <header class="fz-home-journal__heading">
                        <div>
                            <p class="fz-eyebrow"><span></span> مجله فرزین / دانستنی‌های کاربردی</p>
                            <h2 id="fz-home-journal-heading">قبل از انتخاب، آگاهانه‌تر ببینید.</h2>
                            <p>نکته‌ها و مقاله‌های مرتبط با هود و سینک؛ کوتاه، کاربردی و از دل تجربه‌ی تجهیزات آشپزخانه.</p>
                        </div>
                        <a class="fz-home-journal__all" href="{{ route('blog.index') }}">
                            همه مقاله‌ها
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 12H5m7 7-7-7 7-7"/>
                            </svg>
                        </a>
                    </header>

                    <div class="fz-home-journal__layout">
                        <article class="fz-home-journal__featured">
                            <a class="fz-home-journal__image" href="{{ route('blog.show', $homeFeaturedArticle->slug) }}" aria-label="مطالعه {{ $homeFeaturedArticle->title }}">
                                @if($homeFeaturedArticleImage)
                                    <img
                                        src="{{ $homeFeaturedArticleImage }}"
                                        @if($homeFeaturedArticle->cover_image_url && $homeFeaturedArticle->cover_image_srcset) srcset="{{ $homeFeaturedArticle->cover_image_srcset }}" sizes="(max-width: 767px) 100vw, 70vw" @endif
                                        alt="{{ $homeFeaturedArticleImageAlt }}"
                                        width="1200"
                                        height="760"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                @else
                                    <span class="fz-home-journal__image-fallback" aria-hidden="true">F.</span>
                                @endif
                                <span class="fz-home-journal__image-mark">مجله فرزین</span>
                            </a>

                            <div class="fz-home-journal__featured-copy">
                                <div class="fz-home-journal__meta">
                                    @if($homeFeaturedCategory)
                                        <a href="{{ route('blog.category', $homeFeaturedCategory->slug) }}">{{ $homeFeaturedCategory->name }}</a>
                                    @else
                                        <span>مجله فرزین</span>
                                    @endif
                                    <span aria-hidden="true">·</span>
                                    <time datetime="{{ $homeFeaturedArticle->published_at?->toAtomString() }}">{{ \App\Helpers\PersianNumberHelper::format(\App\Helpers\JalaliHelper::format($homeFeaturedArticle->published_at, 'Y/m/d')) }}</time>
                                    <span aria-hidden="true">·</span>
                                    <span>{{ \App\Helpers\PersianNumberHelper::format($homeFeaturedArticle->reading_time) }} دقیقه مطالعه</span>
                                </div>

                                <h3><a href="{{ route('blog.show', $homeFeaturedArticle->slug) }}">{{ $homeFeaturedArticle->title }}</a></h3>

                                @if($homeFeaturedArticle->excerpt)
                                    <p>{{ $homeFeaturedArticle->excerpt }}</p>
                                @endif

                                <a class="fz-home-journal__read" href="{{ route('blog.show', $homeFeaturedArticle->slug) }}">
                                    خواندن مقاله
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M19 12H5m7 7-7-7 7-7"/>
                                    </svg>
                                </a>
                            </div>
                        </article>

                        <aside class="fz-home-journal__aside" aria-label="مقاله‌های بیشتر مجله فرزین">
                            <div class="fz-home-journal__aside-heading">
                                <span>بیشتر بخوانید</span>
                                <span>مجله فرزین</span>
                            </div>

                            @forelse($homeJournalStories as $story)
                                @php
                                    $storyCategory = $story->terms->firstWhere('type', 'category');
                                @endphp
                                <article class="fz-home-journal__story">
                                    <a href="{{ route('blog.show', $story->slug) }}">
                                        @if($storyCategory)
                                            <span class="fz-home-journal__story-category">{{ $storyCategory->name }}</span>
                                        @endif
                                        <h3>{{ $story->title }}</h3>
                                        <span class="fz-home-journal__story-meta">
                                            <time datetime="{{ $story->published_at?->toAtomString() }}">{{ $story->published_at?->format('Y/m/d') }}</time>
                                            <span aria-hidden="true">·</span>
                                            {{ \App\Helpers\PersianNumberHelper::format($story->reading_time) }} دقیقه
                                        </span>
                                    </a>
                                </article>
                            @empty
                                <p class="fz-home-journal__aside-empty">مقاله‌های بیشتری را در مجله فرزین دنبال کنید.</p>
                            @endforelse

                            <a class="fz-home-journal__aside-all" href="{{ route('blog.index') }}">رفتن به آرشیو مجله <span aria-hidden="true">←</span></a>
                        </aside>
                    </div>
                </div>
            </section>
        @endif

        <section class="fz-home-final-cta" aria-labelledby="fz-home-final-heading">
            <div class="fz-container"><div class="fz-home-final-cta__panel">
                <div class="fz-home-final-cta__visual" aria-hidden="true">
                    @if($homeCtaImageUrl)
                        <img src="{{ $homeCtaImageUrl }}" @if($homeCtaImageSrcset) srcset="{{ $homeCtaImageSrcset }}" sizes="(max-width: 767px) 100vw, 48vw" @endif alt="" width="1200" height="900" loading="lazy" decoding="async">
                    @else
                        <span class="fz-home-final-cta__fallback">FARZIN</span>
                    @endif
                    <span class="fz-home-final-cta__image-label">FARZIN / KITCHEN EQUIPMENT</span>
                </div>
                <div class="fz-home-final-cta__copy">
                    <p class="fz-eyebrow"><span></span> قدم بعدی شما</p>
                    <h2 id="fz-home-final-heading">مدل مناسب آشپزخانه‌تان را پیدا کنید.</h2>
                    <p>محصول‌ها را مقایسه کنید و برای بررسی مشخصات یا پرسش پیش از خرید، از مسیرهای ارتباطی فرزین کمک بگیرید.</p>
                    <div class="fz-home-final-cta__actions">
                        <a class="fz-home-final-cta__primary" href="{{ route('shop.index') }}">مشاهده محصولات و قیمت‌ها <span aria-hidden="true">←</span></a>
                        <a class="fz-home-final-cta__secondary" href="{{ route('contact') }}">پرسش از فرزین</a>
                    </div>
                </div>
            </div></div>
        </section>

@endsection
