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
<div class="overflow-hidden bg-[var(--livora-cream)]">
    <section class="relative overflow-hidden bg-[var(--livora-ink)] text-white">
        <div class="pointer-events-none absolute -left-24 -top-28 h-80 w-80 rounded-full border border-white/10"></div>
        <x-layout.container>
            <div class="relative py-14 sm:py-20 lg:py-24">
                <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-xs text-white/55">
                    <a href="{{ route('home') }}" class="transition hover:text-white">خانه</a>
                    <span>/</span>
                    <a href="{{ route('blog.index') }}" class="transition hover:text-white">بلاگ</a>
                    @if($term)<span>/</span><span class="text-[#b7c9cb]">{{ $term->name }}</span>@endif
                </nav>
                <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_0.55fr] lg:items-end">
                    <div class="max-w-4xl">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-[#b7c9cb]">FARZIN / FIELD NOTES</p>
                        <h1 class="mt-5 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-7xl">
                            @if($term)
                                {{ $term->name }}
                            @else
                                انتخاب آگاهانه، <span class="block text-[#b7c9cb]">از همین‌جا شروع می‌شود.</span>
                            @endif
                        </h1>
                        <p class="mt-5 max-w-2xl text-sm leading-8 text-white/65 sm:text-base">{{ $archiveDescription }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                        <a href="{{ route('blog.index') }}" class="rounded-full border border-white/20 px-4 py-2 text-xs text-white/80 transition hover:bg-white/10">همه مقاله‌ها</a>
                        <a href="{{ route('blog.feed') }}" class="rounded-full border border-white/20 px-4 py-2 text-xs text-white/80 transition hover:bg-white/10">RSS Feed</a>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>

    <section class="bg-[var(--livora-white)] py-12 sm:py-16">
        <x-layout.container>
            <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--livora-accent)]">THE BUYING GUIDE</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">{{ $term ? 'مقاله‌های این موضوع' : 'راهنماهای فرزین' }}</h2>
                </div>
                <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-[var(--livora-accent)]">مشاهده محصولات <span aria-hidden="true">←</span></a>
            </div>

            @if($posts->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($posts as $post)
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-[1.75rem] border border-[var(--livora-border)] bg-[var(--livora-cream)] transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-accent)] hover:shadow-[var(--livora-shadow-md)]">
                            <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/10] overflow-hidden bg-[var(--livora-surface)]">
                                @if($post->cover_image_url)
                                    <img src="{{ $post->cover_image_url }}" @if($post->cover_image_srcset) srcset="{{ $post->cover_image_srcset }}" sizes="(max-width: 639px) 90vw, (max-width: 1023px) 45vw, 320px" @endif alt="{{ $post->cover_image_alt ?: $post->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-full items-end bg-gradient-to-br from-[#111416] via-[#354247] to-[#8b9b9f] p-5"><span class="text-2xl font-semibold text-white">{{ $post->title }}</span></div>
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col p-6">
                                <div class="flex flex-wrap items-center gap-2 text-[10px] text-[var(--livora-stone)]">
                                    <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->format('Y/m/d') }}</time>
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $post->reading_time }} دقیقه مطالعه</span>
                                </div>
                                <h3 class="mt-3 text-xl font-semibold leading-8"><a href="{{ route('blog.show', $post->slug) }}" class="transition group-hover:text-[var(--livora-accent)]">{{ $post->title }}</a></h3>
                                @if($post->excerpt)<p class="mt-3 line-clamp-3 text-sm leading-7 text-[var(--livora-stone)]">{{ $post->excerpt }}</p>@endif
                                @if($post->terms->isNotEmpty())
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach($post->terms->where('type', 'category') as $categoryTerm)
                                            <a href="{{ route('blog.category', $categoryTerm->slug) }}" class="rounded-full border border-[var(--livora-border)] px-2.5 py-1 text-[10px] text-[var(--livora-stone)] hover:border-[var(--livora-accent)]">{{ $categoryTerm->name }}</a>
                                        @endforeach
                                    </div>
                                @endif
                                <a href="{{ route('blog.show', $post->slug) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-[var(--livora-accent)]">مطالعه مقاله <span aria-hidden="true">←</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            @else
                <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-cream)] px-6 py-14 text-center">
                    <h3 class="text-xl font-semibold">{{ $term ? 'هنوز مقاله‌ای در این موضوع منتشر نشده است.' : 'مقاله‌های جدید به‌زودی منتشر می‌شوند.' }}</h3>
                    <p class="mx-auto mt-3 max-w-lg text-sm leading-7 text-[var(--livora-stone)]">در این فاصله می‌توانید محصولات و دسته‌بندی‌ها را بررسی کنید.</p>
                    <a href="{{ route('categories.index') }}" class="mt-6 inline-flex rounded-2xl bg-[var(--livora-ink)] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[var(--livora-accent)]">مشاهده دسته‌بندی‌ها</a>
                </div>
            @endif
        </x-layout.container>
    </section>
</div>
@endsection
