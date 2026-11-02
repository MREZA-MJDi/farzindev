@props(['post'])

<article class="fz-journal-card fz-reveal">
    <a class="fz-journal-card__cover" href="{{ route('blog.show', $post->slug) }}" aria-label="خواندن {{ $post->title }}">
        @if($post->cover_image_url)
            <img
                src="{{ $post->cover_image_url }}"
                @if($post->cover_image_srcset) srcset="{{ $post->cover_image_srcset }}" sizes="(max-width: 767px) 88vw, (max-width: 1023px) 44vw, 360px" @endif
                alt="{{ $post->title }}"
                width="720"
                height="480"
                loading="lazy"
                decoding="async"
            >
        @else
            
        @endif
        @if($post->terms->firstWhere('type', 'category'))
            <span class="fz-journal-card__category">{{ $post->terms->firstWhere('type', 'category')->name }}</span>
        @endif
    </a>
    <div class="fz-journal-card__body">
        <div class="fz-journal-card__meta">
            <time datetime="{{ $post->published_at?->toAtomString() }}">{{ \App\Helpers\JalaliHelper::format($post->published_at, 'Y/m/d') }}</time>
            <span>{{ \App\Helpers\PersianNumberHelper::format($post->reading_time) }} دقیقه مطالعه</span>
        </div>
        <h3><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
        <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: $post->plain_text, 150) }}</p>
        <a class="fz-journal-card__read" href="{{ route('blog.show', $post->slug) }}">ادامه مطلب <span aria-hidden="true">←</span></a>
    </div>
</article>
