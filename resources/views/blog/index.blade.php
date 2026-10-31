@extends('layouts.app')

@section('title', 'بلاگ فرزین | راهنمای انتخاب هود و سینک')
@section('description', 'راهنماهای انتخاب، نصب و نگهداری هود آشپزخانه و سینک ظرفشویی از فرزین.')
@section('canonical', route('blog.index'))
@section('og_type', 'website')

@push('seo')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Blog',
    'name' => 'بلاگ فرزین',
    'url' => route('blog.index'),
    'inLanguage' => 'fa-IR',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
<div class="overflow-hidden bg-[var(--livora-cream)]">
    <section class="relative overflow-hidden bg-[var(--livora-ink)] text-white">
        <div class="pointer-events-none absolute -left-24 -top-28 h-80 w-80 rounded-full border border-white/10"></div>
        <x-layout.container>
            <div class="relative py-14 sm:py-20 lg:py-24">
                <nav aria-label="مسیر صفحه" class="flex items-center gap-2 text-xs text-white/55">
                    <a href="{{ route('home') }}" class="transition hover:text-white">خانه</a><span>/</span><span class="text-[#b7c9cb]">بلاگ</span>
                </nav>
                <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_0.55fr] lg:items-end">
                    <div class="max-w-4xl">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-[#b7c9cb]">FARZIN / FIELD NOTES</p>
                        <h1 class="mt-5 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-7xl">انتخاب آگاهانه، <span class="block text-[#b7c9cb]">از همین‌جا شروع می‌شود.</span></h1>
                    </div>
                    <p class="max-w-xl text-sm leading-8 text-white/65 sm:text-base">راهنماهای کاربردی برای شناخت، انتخاب، نصب و نگهداری تجهیزات آشپزخانه؛ نوشته‌هایی که به تصمیم بهتر کمک می‌کنند.</p>
                </div>
            </div>
        </x-layout.container>
    </section>

    <section class="bg-[var(--livora-white)] py-12 sm:py-16">
        <x-layout.container>
            <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--livora-accent)]">THE BUYING GUIDE</p><h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">راهنماهای فرزین</h2></div>
                <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-[var(--livora-accent)]">مشاهده محصولات <span aria-hidden="true">←</span></a>
            </div>

            @if($posts->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($posts as $post)
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-[1.75rem] border border-[var(--livora-border)] bg-[var(--livora-cream)] transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-accent)] hover:shadow-[var(--livora-shadow-md)]">
                            <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/10] overflow-hidden bg-[var(--livora-surface)]">
                                @if($post->cover_image_url)
                                    <img src="{{ $post->cover_image_url }}" @if($post->cover_image_srcset) srcset="{{ $post->cover_image_srcset }}" sizes="(max-width: 639px) 90vw, (max-width: 1023px) 45vw, 320px" @endif alt="{{ $post->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-full items-end bg-gradient-to-br from-[#111416] via-[#354247] to-[#8b9b9f] p-5"><span class="text-2xl font-semibold text-white">{{ $post->title }}</span></div>
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col p-6">
                                <p class="text-[10px] text-[var(--livora-stone)]">{{ $post->published_at?->format('Y/m/d') }}</p>
                                <h3 class="mt-3 text-xl font-semibold leading-8"><a href="{{ route('blog.show', $post->slug) }}" class="transition group-hover:text-[var(--livora-accent)]">{{ $post->title }}</a></h3>
                                @if($post->excerpt)<p class="mt-3 line-clamp-3 text-sm leading-7 text-[var(--livora-stone)]">{{ $post->excerpt }}</p>@endif
                                <a href="{{ route('blog.show', $post->slug) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-[var(--livora-accent)]">مطالعه مقاله <span aria-hidden="true">←</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            @else
                <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-cream)] px-6 py-14 text-center">
                    <h3 class="text-xl font-semibold">مقاله‌های جدید به‌زودی منتشر می‌شوند.</h3>
                    <p class="mx-auto mt-3 max-w-lg text-sm leading-7 text-[var(--livora-stone)]">تیم فرزین در حال آماده‌سازی راهنماهای تخصصی است. در این فاصله می‌توانید محصولات و دسته‌بندی‌ها را بررسی کنید.</p>
                    <a href="{{ route('categories.index') }}" class="mt-6 inline-flex rounded-2xl bg-[var(--livora-ink)] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[var(--livora-accent)]">مشاهده دسته‌بندی‌ها</a>
                </div>
            @endif
        </x-layout.container>
    </section>
</div>
@endsection
