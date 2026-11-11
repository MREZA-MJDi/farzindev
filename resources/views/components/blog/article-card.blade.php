@props([
    'post',
    'variant' => 'archive',
    'delay' => 0,
])

@php
    $postCategory = $post->terms->firstWhere('type', 'category');
    $isHome = $variant === 'home';
@endphp

@if($isHome)
    <article class="fz-home-article-card" data-blog-article-card>
        <a
            class="fz-home-article-card__media"
            href="{{ route('blog.show', $post->slug) }}"
            aria-label="مطالعه {{ $post->title }}"
        >
            @if($post->cover_image_url)
                <img
                    src="{{ $post->cover_image_url }}"
                    @if($post->cover_image_srcset) srcset="{{ $post->cover_image_srcset }}" sizes="(max-width: 767px) 88vw, (max-width: 1023px) 45vw, 360px" @endif
                    alt="{{ $post->cover_image_alt ?: $post->title }}"
                    width="900"
                    height="620"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <span class="fz-home-article-card__media-fallback" aria-hidden="true">
                    <strong>F.</strong>
                    <small>مجله فرزین</small>
                </span>
            @endif
        </a>

        <div class="fz-home-article-card__body">
            <div class="fz-home-article-card__meta">
                @if($postCategory)<span>{{ $postCategory->name }}</span>@endif
                <span>{{ \App\Helpers\PersianNumberHelper::format($post->reading_time) }} دقیقه مطالعه</span>
            </div>
            <h3><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
            @if(filled($post->excerpt))
                <p>{{ \Illuminate\Support\Str::limit($post->excerpt, 125) }}</p>
            @endif
            <a class="fz-home-article-card__link" href="{{ route('blog.show', $post->slug) }}">
                ادامه مطلب <span aria-hidden="true">←</span>
            </a>
        </div>
    </article>
@else
    <article
        class="blog-article-card"
        data-blog-reveal
        data-blog-article-card
        style="--blog-delay: {{ min(max((int) $delay, 0), 360) }}ms"
    >
        <a
            href="{{ route('blog.show', $post->slug) }}"
            class="blog-article-card__media"
            aria-label="مطالعه {{ $post->title }}"
        >
            @if($post->cover_image_url)
                <img
                    src="{{ $post->cover_image_url }}"
                    @if($post->cover_image_srcset) srcset="{{ $post->cover_image_srcset }}" sizes="(max-width: 639px) 100vw, (max-width: 1023px) 50vw, 33vw" @endif
                    alt="{{ $post->cover_image_alt ?: $post->title }}"
                    width="720"
                    height="480"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <span class="blog-art-fallback blog-art-fallback--small" aria-hidden="true">
                    <span class="blog-art-fallback__circle"></span>
                    <span class="blog-art-fallback__word">F.</span>
                </span>
            @endif
            @if($postCategory)
                <span class="blog-article-card__category">{{ $postCategory->name }}</span>
            @endif
        </a>

        <div class="blog-article-card__body">
            <div class="blog-article-card__meta">
                <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->format('Y/m/d') }}</time>
                <span aria-hidden="true">·</span>
                <span>{{ \App\Helpers\PersianNumberHelper::format($post->reading_time) }} دقیقه مطالعه</span>
            </div>

            <h3><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>

            @if($post->excerpt)
                <p>{{ $post->excerpt }}</p>
            @endif

            <a href="{{ route('blog.show', $post->slug) }}" class="blog-article-card__read">
                <span>ادامه‌ی مقاله</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="blog-action-icon" aria-hidden="true">
                    <path d="M19 12H5m7 7-7-7 7-7"/>
                </svg>
            </a>
        </div>
    </article>
@endif
