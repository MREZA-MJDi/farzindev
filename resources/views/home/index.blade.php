@extends('layouts.farzin-public-home')

@section('title', 'فرزین فرجی | هود و سینک آشپزخانه')

@section('description', 'محصولات هود و سینک فرزین فرجی را ببینید و با معیارهای انتخاب درست، تناسب ابعاد، کارایی و جزئیات نصب آشنا شوید.')

@section('canonical', url('/'))

@section('content')
<div class="fz-home">
    <section class="fz-intro" aria-labelledby="fz-home-title">
        <div class="fz-intro__eyebrow">
            <span class="fz-intro__eyebrow-line" aria-hidden="true"></span>
            <span>فرزین فرجی <b>/</b> هود و سینک آشپزخانه</span>
        </div>

        <h1 id="fz-home-title" class="fz-intro__title">
            انتخاب خوب، از
            <span>جزئیات شروع می‌شود.</span>
        </h1>

        <p class="fz-intro__copy">
            هود و سینک فقط بخشی از ظاهر آشپزخانه نیستند؛ هر روز با صدای هود، ابعاد سینک،
            جنس سطوح و راحتی تمیزکاری سروکار داریم. فرزین فرجی در صنعت هود و سینک آشپزخانه
            فعالیت می‌کند و اینجا محصولات را در کنار نکته‌هایی برای انتخاب متناسب‌تر می‌بینید.
        </p>

        <div class="fz-intro__actions">
            <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}">
                مشاهده فروشگاه
                <span aria-hidden="true">↙</span>
            </a>
            <a class="fz-button fz-button--quiet" href="#fz-story">بیشتر درباره فرزین</a>
        </div>

        <div class="fz-intro__rule" aria-hidden="true">
            <span></span><i></i><span></span>
        </div>

        <div class="fz-intro__facts" aria-label="اطلاعات کاتالوگ">
            <div>
                <strong>{{ $products->count() }}</strong>
                <span>محصول تصویردار در این نمایش</span>
            </div>
            <div>
                <strong>{{ $categories->count() }}</strong>
                <span>دسته‌بندی فعال</span>
            </div>
            <div>
                <strong>هود <i>+</i> سینک</strong>
                <span>تمرکز محصولات فرزین</span>
            </div>
        </div>
    </section>

    @if($categories->isNotEmpty())
        <section class="fz-categories" aria-labelledby="fz-categories-title">
            <div class="fz-section-heading fz-section-heading--compact">
                <div>
                    <span class="fz-kicker">انتخاب مسیر</span>
                    <h2 id="fz-categories-title">از دسته‌ای شروع کن که نیازت را جواب می‌دهد.</h2>
                </div>
                <a class="fz-text-link" href="{{ route('categories.index') }}">همه دسته‌بندی‌ها <span aria-hidden="true">←</span></a>
            </div>

            <div class="fz-category-list">
                @foreach($categories as $category)
                    <a class="fz-category-chip" href="{{ route('categories.show', ['category' => $category->slug]) }}">
                        <span>{{ $category->name }}</span>
                        <small>{{ number_format((int) $category->products_count) }} محصول</small>
                        <span class="fz-category-chip__arrow" aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="fz-collection" id="fz-product-grid" aria-labelledby="fz-collection-title">
        <div class="fz-section-heading">
            <div>
                <span class="fz-kicker">کاتالوگ فرزین <b>/</b> مجموعه به‌روز</span>
                <h2 id="fz-collection-title">محصولات را از نزدیک‌تر ببین.</h2>
                <p>شبکه‌ای تصویری از محصولات فعال؛ هر کارت به صفحه مشخصات همان محصول می‌رسد.</p>
            </div>
            <div class="fz-collection__count" aria-label="تعداد محصولات">
                <span>{{ str_pad((string) $products->count(), 2, '0', STR_PAD_LEFT) }}</span>
                <small>کارت محصول</small>
            </div>
        </div>

        @if($products->isNotEmpty())
            <div class="fz-product-grid" data-fz-scroll-grid>
                @foreach($products as $product)
                    @php
                        $image = $product->images->first();
                        $tileVariant = match ($loop->index % 8) {
                            0 => 'feature',
                            2, 6 => 'tall',
                            4 => 'wide',
                            default => 'regular',
                        };
                    @endphp

                    <article class="fz-product-tile fz-product-tile--{{ $tileVariant }}" data-fz-tile>
                        <a class="fz-product-tile__link" href="{{ route('product.show', ['slug' => $product->slug]) }}" aria-label="مشاهده {{ $product->name }}">
                            <div class="fz-product-tile__media" data-fz-image-frame>
                                <span class="fz-product-tile__fallback" aria-hidden="true">FZ</span>
                                <img
                                    class="fz-product-tile__image"
                                    src="{{ $image->url }}"
                                    alt="{{ $image->alt ?: $product->name }}"
                                    loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}"
                                    decoding="async"
                                    fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                                    data-fz-product-image
                                >
                                <span class="fz-product-tile__shade" aria-hidden="true"></span>
                                @if($product->is_new)
                                    <span class="fz-product-tile__badge">تازه</span>
                                @elseif($product->is_featured)
                                    <span class="fz-product-tile__badge">منتخب</span>
                                @endif

                                <div class="fz-product-tile__info">
                                    <span class="fz-product-tile__category">{{ $product->category?->name ?? 'محصول فرزین' }}</span>
                                    <h3>{{ $product->name }}</h3>
                                    @if(filled($product->short_description))
                                        <p>{{ \Illuminate\Support\Str::limit($product->short_description, 92) }}</p>
                                    @endif
                                    <div class="fz-product-tile__bottom">
                                        <strong>{{ number_format((float) $product->price) }} <small>تومان</small></strong>
                                        <span class="fz-product-tile__open" aria-hidden="true">↗</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="fz-empty-state">
                <span class="fz-empty-state__mark" aria-hidden="true">FZ</span>
                <h3>محصول تصویردار برای نمایش آماده نیست.</h3>
                <p>به‌محض فعال‌شدن محصولات و ثبت تصاویرشان در پنل، این بخش به‌صورت خودکار به‌روز می‌شود.</p>
                <a class="fz-button fz-button--primary" href="{{ route('contact') }}">ارتباط با فرزین</a>
            </div>
        @endif
    </section>

    <section class="fz-story" id="fz-story" aria-labelledby="fz-story-title">
        <div class="fz-story__inner">
            <span class="fz-kicker fz-kicker--light">فرزین فرجی <b>/</b> صنعت آشپزخانه</span>
            <h2 id="fz-story-title">محصولی که هر روز استفاده می‌کنی، باید با زندگی واقعی هماهنگ باشد.</h2>
            <p>
                کار فرزین در حوزه هود و سینک آشپزخانه، بخشی از صنعتی است که در آن ظاهر تنها یکی از معیارهاست.
                برای هود، عرض مناسب، مسیر خروج هوا و میزان صدای قابل‌قبول اهمیت دارد؛ برای سینک، ابعاد کابینت،
                عمق لگن، جنس سطح و شیوه نصب تعیین‌کننده‌اند. هدف این صفحه کمک به دیدن محصولات در کنار همین
                جزئیات است تا انتخاب از روی عکس تنها انجام نشود.
            </p>

            <div class="fz-story__principles">
                <article>
                    <span>01</span>
                    <h3>تناسب با فضا</h3>
                    <p>ابعاد محصول و فضای نصب را پیش از انتخاب با هم بسنج.</p>
                </article>
                <article>
                    <span>02</span>
                    <h3>کارایی روزمره</h3>
                    <p>به نگهداری، شیوه استفاده و نیازهای واقعی آشپزخانه توجه کن.</p>
                </article>
                <article>
                    <span>03</span>
                    <h3>جزئیات فنی</h3>
                    <p>مشخصات هر مدل را در صفحه محصول بررسی کن و ابهام‌ها را بپرس.</p>
                </article>
            </div>

            <div class="fz-story__actions">
                <a class="fz-button fz-button--light" href="{{ route('about') }}">آشنایی با مجموعه</a>
                <a class="fz-story__text-link" href="{{ route('contact') }}">سؤال داری؟ با ما در تماس باش <span aria-hidden="true">↗</span></a>
            </div>
        </div>
        <span class="fz-story__outline" aria-hidden="true">F</span>
    </section>

    <section class="fz-last-call" aria-labelledby="fz-last-call-title">
        <div>
            <span class="fz-kicker">قدم بعدی</span>
            <h2 id="fz-last-call-title">مدلی را پیدا کن که به آشپزخانه‌ات می‌خورد.</h2>
            <p>مشخصات را مقایسه کن و اگر برای انتخاب هود یا سینک نیاز به راهنمایی داری، با فرزین در ارتباط باش.</p>
        </div>
        <div class="fz-last-call__actions">
            <a class="fz-button fz-button--primary" href="{{ route('shop.index') }}">رفتن به فروشگاه <span aria-hidden="true">↗</span></a>
            <a class="fz-button fz-button--quiet" href="{{ route('contact') }}">تماس با ما</a>
        </div>
    </section>
</div>
@endsection
