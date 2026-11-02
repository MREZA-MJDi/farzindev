@extends('layouts.app')

@section('title', 'فرزین | خرید هود و سینک آشپزخانه')
@section('description', 'مدل‌های هود و سینک آشپزخانه فرزین را با قیمت و اطلاعات ثبت‌شده بررسی کنید و برای انتخاب مناسب‌تر راهنمایی بگیرید.')
@section('canonical', route('home'))
@section('body_class', 'home-body min-h-screen antialiased')

@section('content')
@php
    $heroImage = $heroProduct?->images->first(fn ($image) => filled($image->url));
    $homeGuides = $categories->filter(fn ($category) => mb_strlen(trim((string) $category->description)) >= 120)->values();
    $homeCtaCategory = $categories->first(fn ($category) => filled($category->homepage_image_url));
    $homeCtaImage = $homeCtaCategory?->homepage_image_url
        ?? $heroImage?->url;

    $homeBenefits = [];
    if ($installmentProduct) {
        $cashPercent = (int) $installmentProduct->installment_cash_percent;
        $homeBenefits[] = [
            'title' => 'خرید اقساطی برای مدل‌های واجد شرایط',
            'description' => 'پیش‌پرداخت ' . \App\Helpers\PersianNumberHelper::format($cashPercent) . '٪، ' .
                \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_cheque_count) .
                ' چک با فاصله ' . \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_interval_months) . ' ماه',
            'url' => route('product.show', $installmentProduct->slug),
            'label' => 'دیدن شرایط همین مدل',
        ];
    }

    $phone = \App\Support\StorefrontContact::phone($contactSetting?->phone);
    $whatsappDigits = \App\Support\StorefrontContact::whatsapp($contactSetting?->whatsapp);

    if (array_key_exists('shipping_flat_rate', $shippingSettings) && is_numeric($shippingSettings['shipping_flat_rate'])) {
        $flatRate = (float) $shippingSettings['shipping_flat_rate'];
        $homeBenefits[] = [
            'title' => 'هزینه ارسال مشخص',
            'description' => $flatRate > 0
                ? 'هزینه پایه ارسال: ' . \App\Helpers\PersianNumberHelper::format($flatRate) . ' تومان'
                : 'هزینه پایه ارسال طبق تنظیمات فروشگاه رایگان است.',
            'url' => route('contact'),
            'label' => 'پرسش درباره ارسال',
        ];
    } elseif (isset($shippingSettings['shipping_free_threshold']) && is_numeric($shippingSettings['shipping_free_threshold']) && (float) $shippingSettings['shipping_free_threshold'] > 0) {
        $homeBenefits[] = [
            'title' => 'آستانه ارسال رایگان',
            'description' => 'برای سفارش‌های بالاتر از ' . \App\Helpers\PersianNumberHelper::format($shippingSettings['shipping_free_threshold']) . ' تومان',
            'url' => route('contact'),
            'label' => 'پرسش درباره ارسال',
        ];
    }

    if ($phone || $whatsappDigits) {
        $homeBenefits[] = [
            'title' => 'راه ارتباطی ثبت‌شده',
            'description' => $phone ? $phone['display'] : 'ارتباط از طریق واتساپ',
            'url' => $phone['href'] ?? ('https://wa.me/' . $whatsappDigits),
            'label' => $phone ? 'تماس با فرزین' : 'گفت‌وگو در واتساپ',
        ];
    }
@endphp

<section class="fz-home-hero" aria-labelledby="fz-home-hero-title">
    <div class="fz-home-hero__ambient" aria-hidden="true">
        <span class="fz-home-hero__ambient-orb fz-home-hero__ambient-orb--one"></span>
        <span class="fz-home-hero__ambient-orb fz-home-hero__ambient-orb--two"></span>
        <span class="fz-home-hero__ambient-grid"></span>
    </div>
    <div class="fz-container fz-home-hero__layout {{ $heroImage ? '' : 'fz-home-hero__layout--copy-only' }}">
        <div class="fz-home-hero__copy">
            <p class="fz-home-hero__eyebrow"><span class="fz-home-hero__eyebrow-mark" aria-hidden="true"></span> هود و سینک آشپزخانه</p>
            <h1 id="fz-home-hero-title"><span>انتخابی هماهنگ با</span><strong>نیاز آشپزخانه شما</strong></h1>
            <p class="fz-home-hero__lead">مدل‌های هود و سینک را با تصویر و قیمت ثبت‌شده ببینید؛ اطلاعات هر محصول را بررسی کنید و آگاهانه انتخاب کنید.</p>
            <div class="fz-home-hero__actions">
                <a class="fz-home-hero__button fz-home-hero__button--primary" href="{{ route('shop.index') }}">مشاهده محصولات</a>
                <a class="fz-home-hero__button fz-home-hero__button--secondary" href="{{ route('contact') }}">مشاوره پیش از خرید</a>
            </div>
        </div>

        @if($heroProduct && $heroImage)
            <a class="fz-home-hero__showcase fz-home-hero__showcase--static" href="{{ route('product.show', $heroProduct->slug) }}" aria-label="مشاهده {{ $heroProduct->name }}">
                <span class="fz-home-hero__showcase-heading">
                    <span><strong>مدل موجود</strong><small>{{ $heroProduct->category?->name }}</small></span>
                    <span class="fz-home-hero__showcase-label">قابل بررسی</span>
                </span>
                <span class="fz-home-hero__product-image">
                    <img
                        src="{{ $heroImage->url }}"
                        @if($heroImage->responsive_srcset) srcset="{{ $heroImage->responsive_srcset }}" sizes="(max-width: 767px) 92vw, 48vw" @endif
                        alt="{{ $heroProduct->name }}"
                        width="960"
                        height="720"
                        fetchpriority="high"
                        loading="eager"
                        decoding="async"
                    >
                </span>
                <span class="fz-home-hero__product-details">
                    <strong>{{ $heroProduct->name }}</strong>
                    <span class="fz-home-hero__product-footer">
                        <span class="fz-home-hero__product-price">{{ \App\Helpers\PersianNumberHelper::format((float) $heroProduct->price) }} <small>تومان</small></span>
                        <span class="fz-home-hero__product-link">مشاهده محصول</span>
                    </span>
                </span>
            </a>
        @endif
    </div>
    <div class="fz-container fz-home-hero__bottom">
        <span>هود آشپزخانه · سینک ظرف‌شویی</span>
        <a href="#product-categories">شروع انتخاب</a>
    </div>
</section>

@if($categories->isNotEmpty())
    <section id="product-categories" class="fz-section fz-category-section" aria-labelledby="fz-category-heading">
        <div class="fz-container">
            <div class="fz-category-heading fz-reveal">
                <p class="fz-eyebrow"><span></span> دسته‌بندی محصولات</p>
                <h2 id="fz-category-heading">از دسته‌ی موردنیازتان شروع کنید.</h2>
                <p>فقط دسته‌هایی را می‌بینید که دست‌کم یک محصول فعال دارند.</p>
            </div>
            <div class="fz-category-grid">
                @foreach($categories as $index => $category)
                    <x-home.category-card :category="$category" :index="$index" />
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($featuredProducts->count() >= 3)
    <section class="fz-section fz-product-section fz-home-featured-section" aria-labelledby="fz-products-heading">
        <div class="fz-container fz-home-featured-section__inner">
            <header class="fz-home-featured-section__heading fz-reveal">
                <div class="fz-home-featured-section__heading-copy">
                    <p class="fz-eyebrow"><span aria-hidden="true"></span> مدل‌های موجود</p>
                    <h2 id="fz-products-heading">مدل‌های منتخب و موجود</h2>
                    <p>مدل‌هایی را ببینید که در حال حاضر امکان خرید دارند.</p>
                </div>
                <a class="fz-home-featured-section__all-link fz-text-link" href="{{ route('shop.index') }}">
                    <span>مشاهده همه محصولات</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                </a>
            </header>
            <div class="fz-product-grid fz-home-featured-section__grid fz-home-featured-grid--six">
                @foreach($featuredProducts as $product)
                    <x-product.card :product="$product" variant="round" :show-availability="false" />
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($installmentProduct)
    <section class="fz-home-installment" aria-labelledby="fz-home-installment-title">
        <div class="fz-container">
            <div class="fz-home-installment__panel">
                <div>
                    <p class="fz-eyebrow"><span></span> شرایط ثبت‌شده محصول</p>
                    <h2 id="fz-home-installment-title">خرید اقساطی برای بعضی مدل‌های موجود</h2>
                    <p>{{ $installmentProduct->name }} با {{ \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_cash_percent) }}٪ پیش‌پرداخت و {{ \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_cheque_count) }} چک، با فاصله سررسید {{ \App\Helpers\PersianNumberHelper::format($installmentProduct->installment_interval_months) }} ماه عرضه شده است.</p>
                </div>
                <a href="{{ route('product.show', $installmentProduct->slug) }}">مشاهده قیمت و جزئیات این مدل</a>
            </div>
        </div>
    </section>
@endif

@if(count($homeBenefits))
    <section class="fz-home-trust" aria-labelledby="fz-home-trust-heading">
        <div class="fz-container">
            <div class="fz-home-trust__intro">
                <p class="fz-eyebrow"><span></span> اطلاعات قابل اتکا برای تصمیم خرید</p>
                <h2 id="fz-home-trust-heading">پیش از انتخاب، جزئیات مهم را بررسی کنید.</h2>
            </div>
            <div class="fz-home-trust__grid">
                @foreach($homeBenefits as $index => $benefit)
                    <a class="fz-home-trust__item" href="{{ $benefit['url'] }}">
                        <span class="fz-home-trust__index">{{ \App\Helpers\PersianNumberHelper::format($index + 1) }}</span>
                        <span class="fz-home-trust__icon" aria-hidden="true">{{ $index === 0 ? '٪' : ($index === 1 ? '↗' : '☎') }}</span>
                        <strong>{{ $benefit['title'] }}</strong>
                        <span>{{ $benefit['description'] }}</span>
                        <em>{{ $benefit['label'] }} <b aria-hidden="true">←</b></em>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($homeGuides->isNotEmpty())
    <section class="fz-section fz-home-buying-guides" aria-labelledby="fz-buying-guides-title">
        <div class="fz-container">
            <header class="fz-home-buying-guides__heading">
                <div>
                    <p class="fz-eyebrow"><span></span> راهنمای انتخاب</p>
                    <h2 id="fz-buying-guides-title">اطلاعات دسته‌بندی را پیش از انتخاب بخوانید.</h2>
                </div>
            </header>
            <div class="fz-home-buying-guides__grid">
                @foreach($homeGuides as $index => $category)
                    <a class="fz-home-buying-guide" href="{{ route('categories.show', $category->slug) }}">
                        <span class="fz-home-buying-guide__index">{{ \App\Helpers\PersianNumberHelper::format($index + 1) }}</span>
                        <span><strong>{{ $category->name }}</strong><small>{{ \Illuminate\Support\Str::limit(trim($category->description), 150) }}</small></span>
                        <span class="fz-home-buying-guide__action">مشاهده مدل‌ها</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($blogPosts->isNotEmpty())
    @php
        $homeFeaturedArticle = $blogPosts->first();
        $homeJournalStories = $blogPosts->skip(1);
        $homeFeaturedCategory = $homeFeaturedArticle->terms->firstWhere('type', 'category');
    @endphp
    <section class="fz-home-journal" aria-labelledby="fz-home-journal-heading">
        <div class="fz-container fz-home-journal__container">
            <header class="fz-home-journal__heading">
                <div>
                    <p class="fz-eyebrow"><span></span> مجله و راهنمای خرید</p>
                    <h2 id="fz-home-journal-heading">پیش از خرید، آگاهانه‌تر انتخاب کنید.</h2>
                    <p>مقاله‌های مرتبط با انتخاب و استفاده از هود و سینک.</p>
                </div>
                <a class="fz-home-journal__all" href="{{ route('blog.index') }}">همه مقاله‌ها</a>
            </header>

            <div class="fz-home-journal__layout">
                <article class="fz-home-journal__featured">
                    @if($homeFeaturedArticle->cover_image_url)
                        <a class="fz-home-journal__image" href="{{ route('blog.show', $homeFeaturedArticle->slug) }}" aria-label="مطالعه {{ $homeFeaturedArticle->title }}">
                            <img
                                src="{{ $homeFeaturedArticle->cover_image_url }}"
                                @if($homeFeaturedArticle->cover_image_srcset) srcset="{{ $homeFeaturedArticle->cover_image_srcset }}" sizes="(max-width: 767px) 100vw, 70vw" @endif
                                alt="{{ $homeFeaturedArticle->title }}"
                                width="1200"
                                height="760"
                                loading="lazy"
                                decoding="async"
                            >
                        </a>
                    @endif
                    <div class="fz-home-journal__featured-copy">
                        <div class="fz-home-journal__meta">
                            @if($homeFeaturedCategory)
                                <a href="{{ route('blog.category', $homeFeaturedCategory->slug) }}">{{ $homeFeaturedCategory->name }}</a>
                            @endif
                            <time datetime="{{ $homeFeaturedArticle->published_at?->toAtomString() }}">{{ \App\Helpers\JalaliHelper::format($homeFeaturedArticle->published_at, 'Y/m/d') }}</time>
                            <span>{{ \App\Helpers\PersianNumberHelper::format($homeFeaturedArticle->reading_time) }} دقیقه مطالعه</span>
                        </div>
                        <h3><a href="{{ route('blog.show', $homeFeaturedArticle->slug) }}">{{ $homeFeaturedArticle->title }}</a></h3>
                        @if($homeFeaturedArticle->excerpt)
                            <p>{{ $homeFeaturedArticle->excerpt }}</p>
                        @endif
                        <a class="fz-home-journal__read" href="{{ route('blog.show', $homeFeaturedArticle->slug) }}">خواندن مقاله <span aria-hidden="true">←</span></a>
                    </div>
                </article>

                @if($homeJournalStories->isNotEmpty())
                    <aside class="fz-home-journal__aside" aria-label="مقاله‌های بیشتر">
                        <div class="fz-home-journal__aside-heading"><span>بیشتر بخوانید</span></div>
                        @foreach($homeJournalStories as $story)
                            @php($storyCategory = $story->terms->firstWhere('type', 'category'))
                            <article class="fz-home-journal__story">
                                <a href="{{ route('blog.show', $story->slug) }}">
                                    @if($storyCategory)
                                        <span class="fz-home-journal__story-category">{{ $storyCategory->name }}</span>
                                    @endif
                                    <h3>{{ $story->title }}</h3>
                                    <span class="fz-home-journal__story-meta">
                                        <time datetime="{{ $story->published_at?->toAtomString() }}">{{ \App\Helpers\JalaliHelper::format($story->published_at, 'Y/m/d') }}</time>
                                        <span aria-hidden="true">·</span>
                                        {{ \App\Helpers\PersianNumberHelper::format($story->reading_time) }} دقیقه
                                    </span>
                                </a>
                            </article>
                        @endforeach
                        <a class="fz-home-journal__aside-all" href="{{ route('blog.index') }}">رفتن به مجله <span aria-hidden="true">←</span></a>
                    </aside>
                @endif
            </div>
        </div>
    </section>
@endif

<section class="fz-home-final-cta" aria-labelledby="fz-home-final-heading">
    <div class="fz-container">
        <div class="fz-home-final-cta__panel {{ $homeCtaImage ? '' : 'fz-home-final-cta__panel--text-only' }}">
            @if($homeCtaImage)
                <div class="fz-home-final-cta__visual" aria-hidden="true">
                    <img src="{{ $homeCtaImage }}" sizes="(max-width: 767px) 100vw, 48vw" alt="" width="1200" height="900" loading="lazy" decoding="async">
                </div>
            @endif
            <div class="fz-home-final-cta__copy">
                <p class="fz-eyebrow"><span></span> قدم بعدی</p>
                <h2 id="fz-home-final-heading">برای انتخاب مدل مناسب، راهنمایی بگیرید.</h2>
                <p>مدل‌های موجود را بررسی کنید یا پرسش خود را درباره محصول با فرزین در میان بگذارید.</p>
                <div class="fz-home-final-cta__actions">
                    <a class="fz-home-final-cta__primary" href="{{ route('contact') }}">تماس و مشاوره <span aria-hidden="true">←</span></a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
