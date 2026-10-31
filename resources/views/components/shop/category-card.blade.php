@props([
    'name',
    'image' => null,
    'imageSrcset' => null,
    'motionMedia' => null,
    'href' => '#',
    'count' => null,
])

@php
    $hasCount = $count !== null;
@endphp

<article class="farzin-category-card group relative min-w-0" data-farzin-reveal>
    <a href="{{ $href }}" class="block" aria-label="مشاهده دسته‌بندی {{ $name }}" @if($motionMedia) data-farzin-gif-card @endif>
        <div class="farzin-category-card__surface relative overflow-hidden rounded-[1.65rem]">
            <div class="farzin-category-card__media relative aspect-[5/4] overflow-hidden">
                @if($image)
                    <img src="{{ $image }}" @if($imageSrcset) srcset="{{ $imageSrcset }}" sizes="(max-width: 639px) 90vw, (max-width: 1023px) 45vw, 320px" @endif alt="{{ $name }}" loading="lazy" decoding="async" class="farzin-category-card__image h-full w-full object-cover">
                @else
                    <div class="farzin-category-fallback flex h-full w-full items-center justify-center">
                        <div class="farzin-category-fallback__mark" aria-hidden="true"><span>FZ</span><i></i></div>
                    </div>
                @endif
                @if($motionMedia?->is_video)
                    <video data-farzin-category-video data-farzin-video-src="{{ $motionMedia->url }}" class="farzin-category-card__gif" muted loop playsinline preload="none" aria-hidden="true" tabindex="-1"></video>
                @elseif($motionMedia)
                    <img data-farzin-category-gif data-farzin-gif-src="{{ $motionMedia->url }}" class="farzin-category-card__gif" alt="" aria-hidden="true" loading="lazy" decoding="async">
                @endif
            </div>
            <div class="farzin-category-card__shade absolute inset-0"></div>
            <div class="absolute left-4 top-4 z-10"><span class="farzin-category-card__signature">FARZIN <i aria-hidden="true"></i></span></div>
            <div class="farzin-category-card__content absolute inset-x-0 bottom-0 z-10 p-5 sm:p-6">
                <div class="flex items-end justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-white/60">KITCHEN SYSTEMS</p>
                        <h3 class="mt-2 truncate text-xl font-semibold text-white sm:text-2xl">{{ $name }}</h3>
                        @if($hasCount)
                            <p class="mt-2 text-xs text-white/65">{{ \App\Helpers\PersianNumberHelper::format($count) }} محصول</p>
                        @endif
                    </div>
                    <span class="farzin-category-card__arrow flex h-11 w-11 shrink-0 items-center justify-center rounded-full" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </span>
                </div>
            </div>
        </div>
    </a>
</article>
