@extends('layouts.app')

@php
    $archiveTitle = $term
        ? ($term->meta_title ?: ($term->name . ' | بلاگ فرزین'))
        : 'مجله فرزین | مقاله‌ها و راهنماهای تجهیزات آشپزخانه';
    $archiveDescription = $term
        ? ($term->meta_description ?: ($term->description ?: ('مقاله‌های مرتبط با ' . $term->name . ' در مجله فرزین.')))
        : 'مقاله‌های کاربردی فرزین درباره هود و سینک آشپزخانه؛ معرفی محصولات، شناخت مشخصات، انتخاب و نگهداری تجهیزات آشپزخانه.';
    $archiveCanonical = $term
        ? ($term->type === 'category' ? route('blog.category', $term->slug) : route('blog.tag', $term->slug))
        : route('blog.index');

    $visiblePosts = $posts->getCollection();
    $featuredPost = $posts->currentPage() === 1 ? $visiblePosts->first() : null;
    $gridPosts = $featuredPost ? $visiblePosts->skip(1) : $visiblePosts;
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

@push('styles')
    @vite('resources/css/blog.css')
@endpush

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

@section('content')
<div class="blog-shell" data-blog-motion style="--blog-container-width: {{ $appearance['container_width'] }}px;">
    <section class="blog-masthead" aria-labelledby="blog-page-title">
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

                <a href="{{ route('blog.feed') }}" class="blog-rss-quiet">فید RSS</a>
            </div>

            <div class="blog-masthead__layout">
                <div class="blog-masthead__copy" data-blog-reveal>
                    <p class="blog-eyebrow">
                        <span class="blog-eyebrow__dot" aria-hidden="true"></span>
                        مجله فرزین
                        <span class="blog-eyebrow__slash">/</span>
                        مجله تخصصی فرزین
                    </p>

                    <h1 id="blog-page-title" class="blog-masthead__title">
                        @if($term)
                            {{ $term->name }}
                            <span>مقاله‌ها و نوشته‌های مرتبط</span>
                        @else
                            مجله فرزین
                            <span>دانستنی‌های هود و سینک آشپزخانه</span>
                        @endif
                    </h1>

                    <p class="blog-masthead__description">{{ $archiveDescription }}</p>

                    <div class="blog-masthead__stats" aria-label="آمار مجله">
                        <div class="blog-stat">
                            <strong>{{ \App\Helpers\PersianNumberHelper::format($posts->total()) }}</strong>
                            <span>مقاله منتشرشده</span>
                        </div>
                        <span class="blog-stat-divider" aria-hidden="true"></span>
                        <div class="blog-stat">
                            <strong>فرزین</strong>
                            <span>مجله تجهیزات آشپزخانه</span>
                        </div>
                    </div>
                </div>

                <div class="blog-masthead__visual" data-blog-reveal>
                    @if($appearance['hero']['image_url'])
                        <div class="blog-user-art" style="--blog-image-fit: {{ $appearance['hero']['fit'] }}; --blog-image-position: {{ $appearance['hero']['object_position'] }}; --blog-image-scale: {{ number_format($appearance['hero']['scale'] / 100, 2, '.', '') }};">
                            <img
                                src="{{ $appearance['hero']['image_url'] }}"
                                @if($appearance['hero']['image_srcset']) srcset="{{ $appearance['hero']['image_srcset'] }}" sizes="(max-width: 767px) 100vw, 42vw" @endif
                                alt="{{ $appearance['hero']['alt'] ?: 'تصویر مجله فرزین' }}"
                                width="960"
                                height="640"
                                fetchpriority="high"
                                decoding="async"
                            >
                        </div>
                    @else
                        <div class="blog-masthead__visual-fallback" aria-hidden="true">
                            <span>F.</span>
                            <small>مجله فرزین</small>
                        </div>
                    @endif
                </div>
            </div>
        </x-layout.container>
    </section>

    @if($featuredPost)
        <section id="blog-featured" class="blog-section blog-featured-section" aria-labelledby="blog-featured-title">
            <x-layout.container>
                <div class="blog-section-heading" data-blog-reveal>
                    <div>
                        <p class="blog-section-kicker">مطلب منتخب مجله <span>— 01</span></p>
                        <h2 id="blog-featured-title">پیشنهاد مطالعه</h2>
                    </div>
                    <p>یک مقاله برای شروع یا عمیق‌تر شدن در انتخاب تجهیزات آشپزخانه.</p>
                </div>

                @php
                    $featuredCategory = $featuredPost->terms->firstWhere('type', 'category');
                @endphp

                <article class="blog-featured-card" data-blog-reveal>
                    <div class="blog-featured-card__content">
                        <div class="blog-featured-card__topline">
                            <span class="blog-featured-card__badge">
                                <span class="blog-featured-card__badge-dot" aria-hidden="true"></span>
                                {{ $featuredPost->is_featured ? 'پیشنهاد سردبیر' : 'آخرین مقاله' }}
                            </span>
                            <span class="blog-featured-card__number">۰۱ / منتخب</span>
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
                            <span>{{ \App\Helpers\PersianNumberHelper::format($featuredPost->reading_time) }} دقیقه مطالعه</span>
                            @if($featuredPost->author)
                                <span aria-hidden="true">·</span>
                                <span>{{ $featuredPost->author->name }}</span>
                            @endif
                        </div>

                        <a href="{{ route('blog.show', $featuredPost->slug) }}" class="blog-featured-card__action">
                            <span>مطالعه مقاله</span>
                            <span class="blog-featured-card__action-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="blog-action-icon">
                                    <path d="M19 12H5m7 7-7-7 7-7"/>
                                </svg>
                            </span>
                        </a>
                    </div>

                    <a href="{{ route('blog.show', $featuredPost->slug) }}" class="blog-featured-card__media" aria-label="مطالعه {{ $featuredPost->title }}">
                        @if($featuredPost->cover_image_url)
                            <img
                                src="{{ $featuredPost->cover_image_url }}"
                                @if($featuredPost->cover_image_srcset) srcset="{{ $featuredPost->cover_image_srcset }}" sizes="(max-width: 767px) 100vw, 52vw" @endif
                                alt="{{ $featuredPost->cover_image_alt ?: $featuredPost->title }}"
                                width="1200"
                                height="800"
                                fetchpriority="high"
                                decoding="async"
                            >
                        @else
                            <span class="blog-art-fallback" aria-hidden="true">
                                <span class="blog-art-fallback__circle"></span>
                                <span class="blog-art-fallback__word">FARZIN<br>JOURNAL</span>
                            </span>
                        @endif
                        <span class="blog-featured-card__media-label">مقاله منتخب فرزین</span>
                    </a>
                </article>
            </x-layout.container>
        </section>
    @endif

    <section id="blog-articles" class="blog-section blog-articles-section" aria-labelledby="blog-articles-title">
        <x-layout.container>
            <div class="blog-section-heading blog-section-heading--articles" data-blog-reveal>
                <div>
                    <p class="blog-section-kicker">آرشیو مقالات <span>— 02</span></p>
                    <h2 id="blog-articles-title">{{ $term ? 'مقاله‌های ' . $term->name : 'تازه‌های مجله' }}</h2>
                </div>
                <p>{{ \App\Helpers\PersianNumberHelper::format($posts->total()) }} مقاله برای مطالعه و بررسی.</p>
            </div>

            @if($topics->isNotEmpty() || $term)
                <nav class="blog-topics" aria-label="دسته‌بندی مقالات" data-blog-reveal>
                    <a href="{{ route('blog.index') }}" @class(['blog-topic', 'is-active' => ! $term])>همه مقاله‌ها</a>
                    @if($term && $term->type === 'tag')
                        <a href="{{ route('blog.tag', $term->slug) }}" class="blog-topic is-active">{{ $term->name }}</a>
                    @endif
                    @foreach($topics as $topic)
                        <a
                            href="{{ route('blog.category', $topic->slug) }}"
                            @class(['blog-topic', 'is-active' => $term && $term->type === 'category' && $term->id === $topic->id])
                        >
                            <span>{{ $topic->name }}</span>
                            <small>{{ \App\Helpers\PersianNumberHelper::format($topic->posts_count) }}</small>
                        </a>
                    @endforeach
                </nav>
            @endif

            @if($gridPosts->isNotEmpty())
                <div class="blog-article-grid">
                    @foreach($gridPosts as $post)
                        <x-blog.article-card :post="$post" :delay="min($loop->index, 8) * 45" />
                    @endforeach
                </div>
            @elseif(! $featuredPost)
                <div class="blog-empty-state" data-blog-reveal>
                    <span class="blog-empty-state__mark" aria-hidden="true">F.</span>
                    <p class="blog-section-kicker">مجله فرزین</p>
                    <h3>{{ $term ? 'هنوز مقاله‌ای در این دسته منتشر نشده است.' : 'هنوز مقاله‌ای منتشر نشده است.' }}</h3>
                    <p>به‌زودی نوشته‌های تازه‌ی فرزین در این بخش قرار می‌گیرند.</p>
                </div>
            @endif

            @if($posts->hasPages())
                <div class="blog-pagination" data-blog-reveal>
                    {{ $posts->links() }}
                </div>
            @endif
        </x-layout.container>
    </section>
</div>
@endsection
