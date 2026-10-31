@extends('layouts.app')

@php
    $archiveTitle = $term
        ? ($term->meta_title ?: ($term->name . ' | بلاگ فرزین'))
        : 'بلاگ فرزین | راهنمای انتخاب هود و سینک';
    $archiveDescription = $term
        ? ($term->meta_description ?: ($term->description ?: ('مقاله‌های مرتبط با ' . $term->name . ' در مجله فرزین.')))
        : 'راهنماهای انتخاب، نصب و نگهداری هود آشپزخانه و سینک ظرفشویی از فرزین.';
    $archiveCanonical = $term
        ? ($term->type === 'category' ? route('blog.category', $term->slug) : route('blog.tag', $term->slug))
        : route('blog.index');

    $visiblePosts = $posts->getCollection();
    $featuredPost = $visiblePosts->first();
    $gridPosts = $visiblePosts->skip(1);
    $topicTerms = $visiblePosts
        ->flatMap(fn ($post) => $post->terms)
        ->where('type', 'category')
        ->unique('id')
        ->take(7);
@endphp

@section('title', $archiveTitle)
@section('description', $archiveDescription)
@section('canonical', $archiveCanonical)
@if($term && $term->seo_robots !== 'inherit')
    @section('robots', $term->seo_robots)
@endif
@section('og_type', 'website')
@section('og_title', $archiveTitle)
@section('og_description', $archiveDescription)

@push('seo')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => $term ? 'CollectionPage' : 'Blog',
    'name' => $archiveTitle,
    'description' => $archiveDescription,
    'url' => $archiveCanonical,
    'inLanguage' => 'fa-IR',
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => 'فرزین',
        'url' => route('home'),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@push('styles')
    @vite(['resources/css/blog.css'])
@endpush


@section('content')
<div class="blog-shell" data-blog-motion style="--blog-container-width: {{ $appearance['container_width'] }}px;">
    <section class="blog-masthead" aria-labelledby="blog-page-title">
        <div class="blog-masthead__grid" aria-hidden="true"></div>
        <div class="blog-masthead__glow blog-masthead__glow--one" aria-hidden="true"></div>
        <div class="blog-masthead__glow blog-masthead__glow--two" aria-hidden="true"></div>

        <x-layout.container>
            <div class="blog-masthead__top">
                <nav aria-label="مسیر صفحه" class="blog-breadcrumb">
                    <a href="{{ route('home') }}">خانه</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('blog.index') }}">مجله</a>
                    @if($term)
                        <span aria-hidden="true">/</span>
                        <span aria-current="page">{{ $term->name }}</span>
                    @endif
                </nav>

                <a href="{{ route('blog.feed') }}" class="blog-rss-link">
                    <span class="blog-rss-link__icon" aria-hidden="true">↗</span>
                    دنبال‌کردن از طریق RSS
                </a>
            </div>

            <div class="blog-masthead__layout">
                <div class="blog-masthead__copy" data-blog-reveal>
                    <p class="blog-eyebrow"><span class="blog-eyebrow__dot"></span> FARZIN JOURNAL <span class="blog-eyebrow__slash">/</span> خانه، با فکر بهتر</p>

                    <h1 id="blog-page-title" class="blog-masthead__title">
                        @if($term)
                            {{ $term->name }}
                            <span>از نگاه فرزین.</span>
                        @else
                            برای خانه‌ای
                            <span>که به سلیقه‌ی تو فکر شده.</span>
                        @endif
                    </h1>

                    <p class="blog-masthead__description">{{ $archiveDescription }}</p>

                    <div class="blog-masthead__actions">
                        <a href="#blog-articles" class="blog-button blog-button--light">
                            کشف مقاله‌ها
                            <span aria-hidden="true">↓</span>
                        </a>
                        <a href="{{ route('categories.index') }}" class="blog-text-link">
                            دیدن محصولات
                            <span aria-hidden="true">↗</span>
                        </a>
                    </div>
                </div>

                <div class="blog-masthead__visual" data-blog-reveal aria-hidden="true">
                    <div class="blog-orbit blog-orbit--outer"></div>
                    <div class="blog-orbit blog-orbit--middle"></div>
                    <div class="blog-orbit blog-orbit--inner"></div>
                    @if($appearance['hero']['image_url'])
                        <div class="blog-user-art" style="--blog-image-fit: {{ $appearance['hero']['fit'] }}; --blog-image-position: {{ $appearance['hero']['object_position'] }}; --blog-image-scale: {{ number_format($appearance['hero']['scale'] / 100, 2, '.', '') }};">
                            <img
                                src="{{ $appearance['hero']['image_url'] }}"
                                @if($appearance['hero']['image_srcset']) srcset="{{ $appearance['hero']['image_srcset'] }}" sizes="(max-width: 767px) 65vw, 320px" @endif
                                alt="{{ $appearance['hero']['alt'] ?: 'تصویر شاخص مجله فرزین' }}"
                                width="640"
                                height="640"
                                fetchpriority="high"
                                decoding="async"
                            >
                        </div>
                    @else
                        <div class="blog-orbit__core"><span>F</span></div>
                    @endif
                    <div class="blog-visual-note blog-visual-note--top">
                        <span class="blog-visual-note__spark"></span>
                        <span>DESIGN YOUR EVERYDAY</span>
                    </div>
                    <div class="blog-visual-note blog-visual-note--bottom">
                        <strong>01—25</strong>
                        <span>IDEAS FOR BETTER LIVING</span>
                    </div>
                    <span class="blog-visual-index">F / J</span>
                </div>
            </div>

            <div class="blog-masthead__bottom">
                <div class="blog-masthead__stats">
                    <div class="blog-stat">
                        <strong>{{ number_format($posts->total()) }}</strong>
                        <span>یادداشت و راهنما</span>
                    </div>
                    <span class="blog-stat-divider" aria-hidden="true"></span>
                    <div class="blog-stat">
                        <strong>FARZIN</strong>
                        <span>برای انتخاب آگاهانه</span>
                    </div>
                </div>
                <a href="#blog-featured" class="blog-scroll-cue">
                    <span class="blog-scroll-cue__line"></span>
                    کمی پایین‌تر، چیزهای خوب هست
                </a>
            </div>
        </x-layout.container>
    </section>

    @if($featuredPost)
        <section id="blog-featured" class="blog-section blog-featured-section" aria-labelledby="blog-featured-title">
            <x-layout.container>
                <div class="blog-section-heading" data-blog-reveal>
                    <div>
                        <p class="blog-section-kicker">THE EDITOR'S DESK <span>— 01</span></p>
                        <h2 id="blog-featured-title">از اینجا شروع کن.</h2>
                    </div>
                    <p>یک انتخاب خوب، از یک سؤال درست شروع می‌شود.</p>
                </div>

                @php
                    $featuredCategory = $featuredPost->terms->firstWhere('type', 'category');
                @endphp

                <article class="blog-featured-card" data-blog-reveal>
                    <div class="blog-featured-card__content">
                        <div class="blog-featured-card__topline">
                            <span class="blog-featured-card__badge">
                                <span class="blog-featured-card__badge-dot"></span>
                                {{ $featuredPost->is_featured ? 'پیشنهاد مجله' : 'تازه‌ترین نوشته' }}
                            </span>
                            <span class="blog-featured-card__number">01 / FEATURED</span>
                        </div>

                        @if($featuredCategory)
                            <a href="{{ route('blog.category', $featuredCategory->slug) }}" class="blog-featured-card__category">
                                {{ $featuredCategory->name }}
                            </a>
                        @endif

                        <h3>
                            <a href="{{ route('blog.show', $featuredPost->slug) }}">{{ $featuredPost->title }}</a>
                        </h3>

                        @if($featuredPost->excerpt)
                            <p class="blog-featured-card__excerpt">{{ $featuredPost->excerpt }}</p>
                        @endif

                        <div class="blog-featured-card__meta">
                            <time datetime="{{ $featuredPost->published_at?->toAtomString() }}">{{ $featuredPost->published_at?->format('Y/m/d') }}</time>
                            <span aria-hidden="true">·</span>
                            <span>{{ $featuredPost->reading_time }} دقیقه مطالعه</span>
                            @if($featuredPost->author)
                                <span aria-hidden="true">·</span>
                                <span>{{ $featuredPost->author->name }}</span>
                            @endif
                        </div>

                        <a href="{{ route('blog.show', $featuredPost->slug) }}" class="blog-featured-card__action">
                            <span>خواندن این راهنما</span>
                            <span class="blog-featured-card__action-icon" aria-hidden="true">↗</span>
                        </a>

                        <span class="blog-featured-card__watermark" aria-hidden="true">F.</span>
                    </div>

                    <a href="{{ route('blog.show', $featuredPost->slug) }}" class="blog-featured-card__media" aria-label="مطالعه {{ $featuredPost->title }}">
                        @if($featuredPost->cover_image_url)
                            <img
                                src="{{ $featuredPost->cover_image_url }}"
                                @if($featuredPost->cover_image_srcset) srcset="{{ $featuredPost->cover_image_srcset }}" sizes="(max-width: 767px) 100vw, 54vw" @endif
                                alt="{{ $featuredPost->cover_image_alt ?: $featuredPost->title }}"
                                width="1200"
                                height="900"
                                fetchpriority="high"
                                decoding="async"
                            >
                        @else
                            <span class="blog-art-fallback">
                                <span class="blog-art-fallback__circle"></span>
                                <span class="blog-art-fallback__word">FARZIN<br>JOURNAL</span>
                                <span class="blog-art-fallback__caption">{{ $featuredPost->title }}</span>
                            </span>
                        @endif
                        <span class="blog-featured-card__media-label">FARZIN / FIELD NOTE</span>
                        <span class="blog-featured-card__media-arrow" aria-hidden="true">↗</span>
                    </a>
                </article>
            </x-layout.container>
        </section>
    @endif

    <section id="blog-articles" class="blog-section blog-articles-section" aria-labelledby="blog-articles-title">
        <x-layout.container>
            <div class="blog-section-heading blog-section-heading--articles" data-blog-reveal>
                <div>
                    <p class="blog-section-kicker">THE LATEST STORIES <span>— 02</span></p>
                    <h2 id="blog-articles-title">{{ $term ? 'بیشتر در ' . $term->name : 'تازه‌های مجله' }}</h2>
                </div>
                <p>ایده‌های کوچک، برای تصمیم‌هایی که هر روز با آن‌ها زندگی می‌کنی.</p>
            </div>

            @if($topicTerms->isNotEmpty() || $term)
                <nav class="blog-topics" aria-label="موضوع‌های مجله" data-blog-reveal>
                    <a href="{{ route('blog.index') }}" @class(['blog-topic', 'is-active' => ! $term])>همه نوشته‌ها</a>
                    @if($term)
                        <a href="{{ $term->type === 'category' ? route('blog.category', $term->slug) : route('blog.tag', $term->slug) }}" class="blog-topic is-active">{{ $term->name }}</a>
                    @endif
                    @foreach($topicTerms as $topic)
                        @if(! $term || $topic->id !== $term->id)
                            <a href="{{ route('blog.category', $topic->slug) }}" class="blog-topic">{{ $topic->name }}</a>
                        @endif
                    @endforeach
                </nav>
            @endif

            @if($gridPosts->isNotEmpty())
                <div class="blog-article-grid">
                    @foreach($gridPosts as $post)
                        @php
                            $postCategory = $post->terms->firstWhere('type', 'category');
                        @endphp
                        <article class="blog-article-card" data-blog-reveal style="--blog-delay: {{ min($loop->index, 8) * 55 }}ms">
                            <a href="{{ route('blog.show', $post->slug) }}" class="blog-article-card__media" aria-label="مطالعه {{ $post->title }}">
                                @if($post->cover_image_url)
                                    <img
                                        src="{{ $post->cover_image_url }}"
                                        @if($post->cover_image_srcset) srcset="{{ $post->cover_image_srcset }}" sizes="(max-width: 639px) 100vw, (max-width: 1023px) 50vw, 33vw" @endif
                                        alt="{{ $post->cover_image_alt ?: $post->title }}"
                                        width="720"
                                        height="520"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                @else
                                    <span class="blog-art-fallback blog-art-fallback--small">
                                        <span class="blog-art-fallback__circle"></span>
                                        <span class="blog-art-fallback__word">F.</span>
                                    </span>
                                @endif
                                @if($postCategory)
                                    <span class="blog-article-card__category">{{ $postCategory->name }}</span>
                                @endif
                                <span class="blog-article-card__media-arrow" aria-hidden="true">↗</span>
                            </a>

                            <div class="blog-article-card__body">
                                <div class="blog-article-card__meta">
                                    <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->format('Y/m/d') }}</time>
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $post->reading_time }} دقیقه مطالعه</span>
                                </div>

                                <h3><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>

                                @if($post->excerpt)
                                    <p>{{ $post->excerpt }}</p>
                                @endif

                                <a href="{{ route('blog.show', $post->slug) }}" class="blog-article-card__read">
                                    <span>ادامه‌ی مطلب</span>
                                    <span aria-hidden="true">←</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @elseif(! $featuredPost)
                <div class="blog-empty-state" data-blog-reveal>
                    <span class="blog-empty-state__mark" aria-hidden="true">F.</span>
                    <p class="blog-section-kicker">A LITTLE PAUSE</p>
                    <h3>{{ $term ? 'هنوز نوشته‌ای در این موضوع منتشر نشده.' : 'اولین داستان به‌زودی اینجا منتشر می‌شود.' }}</h3>
                    <p>تا آن زمان، می‌توانی نگاهی به مجموعه‌ی محصولات و انتخاب‌های فرزین بیندازی.</p>
                    <a href="{{ route('categories.index') }}" class="blog-button blog-button--light">دیدن دسته‌بندی محصولات <span aria-hidden="true">↗</span></a>
                </div>
            @endif

            @if($posts->hasPages())
                <div class="blog-pagination" data-blog-reveal>
                    {{ $posts->links() }}
                </div>
            @endif
        </x-layout.container>
    </section>

    <section class="blog-closing-section">
        <x-layout.container>
            <div class="blog-closing-card" data-blog-reveal>
                <div class="blog-closing-card__copy">
                    <p class="blog-section-kicker">THOUGHTFUL CHOICES, BETTER SPACES</p>
                    <h2>الهام بگیر. دقیق‌تر انتخاب کن.</h2>
                    <p>راهنماها را بخوان و بعد، با خیال راحت‌تر سراغ انتخاب محصول مناسب خانه‌ات برو.</p>
                    <a href="{{ route('categories.index') }}" class="blog-button blog-button--dark">
                        گشت‌وگذار در محصولات
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
                <div class="blog-closing-card__art" @if(! $appearance['closing']['image_url']) aria-hidden="true" @endif>
                    @if($appearance['closing']['image_url'])
                        <img
                            class="blog-closing-card__image"
                            src="{{ $appearance['closing']['image_url'] }}"
                            @if($appearance['closing']['image_srcset']) srcset="{{ $appearance['closing']['image_srcset'] }}" sizes="(max-width: 767px) 35vw, 260px" @endif
                            alt="{{ $appearance['closing']['alt'] ?: 'تصویر بخش پایانی مجله فرزین' }}"
                            width="640"
                            height="640"
                            loading="lazy"
                            decoding="async"
                            style="--blog-image-fit: {{ $appearance['closing']['fit'] }}; --blog-image-position: {{ $appearance['closing']['object_position'] }}; --blog-image-scale: {{ number_format($appearance['closing']['scale'] / 100, 2, '.', '') }};"
                        >
                    @else
                        <span class="blog-closing-card__ring"></span>
                        <span class="blog-closing-card__ring blog-closing-card__ring--two"></span>
                        <span class="blog-closing-card__letter">F.</span>
                    @endif
                </div>
            </div>
        </x-layout.container>
    </section>
</div>
@endsection
