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
        ->merge($post->tags->pluck('name'))
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
    'articleSection' => $post->categories->pluck('name')->values()->all(),
    'inLanguage' => 'fa-IR',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
<article class="bg-[var(--livora-cream)]">
    <header class="bg-[var(--livora-ink)] text-white">
        <x-layout.container>
            <div class="mx-auto max-w-4xl py-12 sm:py-16">
                <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-xs text-white/55">
                    <a href="{{ route('home') }}" class="hover:text-white">خانه</a>
                    <span>/</span>
                    <a href="{{ route('blog.index') }}" class="hover:text-white">بلاگ</a>
                    @foreach($post->categories as $categoryTerm)
                        <span>/</span>
                        <a href="{{ route('blog.category', $categoryTerm->slug) }}" class="hover:text-white">{{ $categoryTerm->name }}</a>
                    @endforeach
                    <span>/</span><span class="text-white/80">{{ $post->title }}</span>
                </nav>

                <p class="mt-9 text-[10px] font-semibold uppercase tracking-[0.22em] text-[#b7c9cb]">FARZIN / ARTICLE</p>
                <h1 class="mt-4 text-3xl font-semibold leading-tight sm:text-5xl">{{ $post->title }}</h1>
                @if($post->excerpt)
                    <p class="mt-5 max-w-3xl text-sm leading-8 text-white/70 sm:text-base">{{ $post->excerpt }}</p>
                @endif
                <div class="mt-5 flex flex-wrap items-center gap-3 text-xs text-white/50">
                    <time datetime="{{ $post->published_at?->toAtomString() }}">انتشار: {{ $post->published_at?->format('Y/m/d') }}</time>
                    <span aria-hidden="true">·</span>
                    <span>{{ $post->reading_time }} دقیقه مطالعه</span>
                    @if($post->author)<span aria-hidden="true">·</span><span>{{ $post->author->name }}</span>@endif
                </div>
                @if($post->terms->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach($post->categories as $categoryTerm)
                            <a href="{{ route('blog.category', $categoryTerm->slug) }}" class="rounded-full border border-white/20 px-3 py-1.5 text-[11px] text-white/80 hover:bg-white/10">{{ $categoryTerm->name }}</a>
                        @endforeach
                        @foreach($post->tags as $tagTerm)
                            <a href="{{ route('blog.tag', $tagTerm->slug) }}" class="rounded-full bg-white/10 px-3 py-1.5 text-[11px] text-white/75 hover:bg-white/20">#{{ $tagTerm->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-layout.container>
    </header>

    <x-layout.container>
        <div class="mx-auto grid max-w-6xl gap-10 py-10 sm:py-14 lg:grid-cols-[minmax(0,1fr)_280px]">
            <div class="min-w-0">
                @if($post->cover_image_url)
                    <img
                        src="{{ $post->cover_image_url }}"
                        @if($post->cover_image_srcset) srcset="{{ $post->cover_image_srcset }}" sizes="(max-width: 1023px) 100vw, 72vw" @endif
                        alt="{{ $post->cover_image_alt ?: $post->title }}"
                        width="1200"
                        height="675"
                        fetchpriority="high"
                        decoding="async"
                        class="mb-9 aspect-[16/9] w-full rounded-[1.75rem] object-cover"
                    >
                @endif

                <div class="rounded-[1.75rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-10">
                    @if(filled($post->body_html))
                        <div class="blog-article-content break-words text-sm leading-9 text-[var(--livora-text)] sm:text-base">{!! $post->body_html !!}</div>
                    @else
                        <div class="whitespace-pre-line break-words text-sm leading-9 text-[var(--livora-text)] sm:text-base">{{ $post->body }}</div>
                    @endif
                </div>

                <div class="mt-8">
                    <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[var(--livora-accent)]"><span aria-hidden="true">→</span> بازگشت به همه مقاله‌ها</a>
                </div>
            </div>

            <aside class="space-y-5">
                <div class="rounded-[1.5rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[var(--livora-accent)]">KEEP EXPLORING</p>
                    <h2 class="mt-3 text-lg font-semibold">راهنماهای بیشتر</h2>
                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">برای انتخاب دقیق‌تر، مشخصات و مدل‌های موجود را کنار توصیه‌های مقاله بررسی کنید.</p>
                    <a href="{{ route('categories.index') }}" class="mt-4 inline-flex text-sm font-semibold text-[var(--livora-accent)]">دسته‌بندی محصولات ←</a>
                </div>

                @if($relatedPosts->isNotEmpty())
                    <div class="rounded-[1.5rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">
                        <h2 class="text-base font-semibold">مقاله‌های مرتبط</h2>
                        <div class="mt-4 space-y-4">
                            @foreach($relatedPosts as $related)
                                <a href="{{ route('blog.show', $related->slug) }}" class="block border-b border-[var(--livora-border)] pb-4 last:border-0 last:pb-0">
                                    <span class="text-sm font-medium leading-6 transition hover:text-[var(--livora-accent)]">{{ $related->title }}</span>
                                    <span class="mt-1 block text-[10px] text-[var(--livora-stone)]">{{ $related->published_at?->format('Y/m/d') }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </x-layout.container>
</article>
@endsection
