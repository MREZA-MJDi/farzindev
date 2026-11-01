@extends('layouts.app')

@php
    $articleTitle = $post->meta_title ?: ($post->title . ' | بلاگ فرزین');
    $articleDescription = $post->meta_description ?: ($post->excerpt ?: 'راهنمای تخصصی فرزین برای انتخاب تجهیزات آشپزخانه.');
    $articleCanonical = $post->canonical_url ?: route('blog.show', $post->slug);
    $articleOgTitle = $post->og_title ?: $articleTitle;
    $articleOgDescription = $post->og_description ?: $articleDescription;
    $articleSchemaType = in_array($post->schema_type, ['BlogPosting', 'Article', 'NewsArticle'], true)
        ? $post->schema_type
        : 'BlogPosting';
    $articleKeywords = collect([$post->focus_keyword])
        ->merge($post->terms->where('type', 'tag')->pluck('name'))
        ->filter()
        ->unique()
        ->values()
        ->all();
    $articleText = $post->plain_text;
    $articleWordCount = count(preg_split('/\s+/u', trim($articleText), -1, PREG_SPLIT_NO_EMPTY) ?: []);
    $articleAuthor = $post->author
        ? ['@type' => 'Person', 'name' => $post->author->name]
        : ['@type' => 'Organization', 'name' => 'فرزین'];
@endphp

@section('title', $articleTitle)
@section('description', $articleDescription)
@section('canonical', $articleCanonical)
@if($post->seo_robots !== 'inherit')
    @section('robots', $post->seo_robots)
@endif
@section('og_type', 'article')
@section('og_title', $articleOgTitle)
@section('og_description', $articleOgDescription)
@section('twitter_card', $post->cover_image_url ? 'summary_large_image' : 'summary')

@push('styles')
    @vite('resources/css/blog.css')
@endpush
@if($post->cover_image_url)
    @section('og_image', $post->cover_image_url)
@endif

@push('seo')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => $articleSchemaType,
    'headline' => $post->title,
    'description' => $articleDescription,
    'articleBody' => mb_substr($articleText, 0, 12000),
    'wordCount' => $articleWordCount,
    'datePublished' => $post->published_at?->toAtomString(),
    'dateModified' => $post->updated_at?->toAtomString(),
    'mainEntityOfPage' => $articleCanonical,
    'image' => $post->cover_image_url ? [$post->cover_image_url] : null,
    'author' => $articleAuthor,
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'فرزین',
    ],
    'keywords' => $articleKeywords,
    'articleSection' => $post->terms->where('type', 'category')->pluck('name')->values()->all(),
    'inLanguage' => 'fa-IR',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
<article class="blog-article-page" data-blog-motion>
    <header class="blog-article-hero">
        <x-layout.container>
            <div class="blog-article-hero__inner">
                <nav aria-label="مسیر صفحه" class="blog-article-breadcrumb">
                    <a href="{{ route('home') }}">خانه</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('blog.index') }}">مجله فرزین</a>
                    @foreach($post->terms->where('type', 'category') as $categoryTerm)
                        <span aria-hidden="true">/</span>
                        <a href="{{ route('blog.category', $categoryTerm->slug) }}">{{ $categoryTerm->name }}</a>
                    @endforeach
                </nav>

                <p class="blog-article-kicker">FARZIN JOURNAL <span>/</span> ARTICLE</p>
                <h1 class="blog-article-title">{{ $post->title }}</h1>

                @if($post->excerpt)
                    <p class="blog-article-excerpt">{{ $post->excerpt }}</p>
                @endif

                <div class="blog-article-meta">
                    <time datetime="{{ $post->published_at?->toAtomString() }}">انتشار: {{ $post->published_at?->format('Y/m/d') }}</time>
                    <span aria-hidden="true">·</span>
                    <span>{{ \App\Helpers\PersianNumberHelper::format($post->reading_time) }} دقیقه مطالعه</span>
                    @if($post->author)
                        <span aria-hidden="true">·</span>
                        <span>{{ $post->author->name }}</span>
                    @endif
                </div>

                @if($post->terms->isNotEmpty())
                    <div class="blog-article-terms" aria-label="موضوع‌های مقاله">
                        @foreach($post->categories as $categoryTerm)
                            <a href="{{ route('blog.category', $categoryTerm->slug) }}">{{ $categoryTerm->name }}</a>
                        @endforeach
                        @foreach($post->terms->where('type', 'tag') as $tagTerm)
                            <a href="{{ route('blog.tag', $tagTerm->slug) }}">#{{ $tagTerm->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-layout.container>
    </header>

    <x-layout.container>
        <div class="blog-article-layout">
            <main class="blog-article-main">
                @if($post->cover_image_url)
                    <img
                        src="{{ $post->cover_image_url }}"
                        @if($post->cover_image_srcset) srcset="{{ $post->cover_image_srcset }}" sizes="(max-width: 1023px) 100vw, 72vw" @endif
                        alt="{{ $post->cover_image_alt ?: $post->title }}"
                        width="1200"
                        height="675"
                        fetchpriority="high"
                        decoding="async"
                        class="blog-article-cover"
                    >
                @endif

                <div class="blog-article-panel">
                    @if(filled($post->body_html))
                        <div class="blog-article-content">{!! $post->body_html !!}</div>
                    @else
                        <div class="blog-article-content blog-article-content--plain">{{ $post->body }}</div>
                    @endif
                </div>

                <a href="{{ route('blog.index') }}" class="blog-article-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5m7 7-7-7 7-7"/>
                    </svg>
                    بازگشت به مجله
                </a>
            </main>

            <aside class="blog-article-sidebar">
                <section class="blog-article-sidebar-card">
                    <p class="blog-section-kicker">FARZIN / CATALOG</p>
                    <h2>حالا مدل‌ها را ببینید.</h2>
                    <p>برای بررسی قیمت، تصاویر و مشخصات هود و سینک، وارد کاتالوگ محصولات شوید.</p>
                    <a href="{{ route('shop.index') }}">
                        مشاهده محصولات
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5m7 7-7-7 7-7"/>
                        </svg>
                    </a>
                </section>

                @if($relatedPosts->isNotEmpty())
                    <section class="blog-article-sidebar-card">
                        <h2>مقاله‌های مرتبط</h2>
                        <div class="blog-related-list">
                            @foreach($relatedPosts as $related)
                                <a href="{{ route('blog.show', $related->slug) }}">
                                    <span>{{ $related->title }}</span>
                                    <small>{{ $related->published_at?->format('Y/m/d') }}</small>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </x-layout.container>
</article>
@endsection
