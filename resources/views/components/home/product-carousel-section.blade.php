@props([
    'id',
    'title',
    'eyebrow' => '',
    'description' => '',
    'products',
])

<section class="fz-home-shelf" id="{{ $id }}" aria-labelledby="{{ $id }}-title" data-fz-carousel>
    <div class="fz-home-container">
        <header class="fz-home-section-head">
            <div>
                @if($eyebrow !== '')<span class="fz-home-section-head__eyebrow">{{ $eyebrow }}</span>@endif
                <h2 id="{{ $id }}-title">{{ $title }}</h2>
                @if($description !== '')
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-[var(--livora-stone)]">{{ $description }}</p>
                @endif
            </div>
            <div class="fz-home-shelf__tools">
                <a href="{{ route('shop.index') }}" class="fz-home-section-head__link">مشاهده فروشگاه <span aria-hidden="true">←</span></a>
                <div class="fz-home-shelf__arrows" role="group" aria-label="حرکت در محصولات {{ $title }}">
                    <button type="button" data-fz-carousel-prev aria-label="محصول قبلی">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
                    </button>
                    <button type="button" data-fz-carousel-next aria-label="محصول بعدی">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </header>

        <div class="fz-home-shelf__track" data-fz-carousel-track tabindex="0" aria-label="فهرست افقی محصولات {{ $title }}">
            @foreach($products as $product)
                <div class="fz-home-shelf__item">
                    <x-product.card :product="$product" :reveal="false" variant="home-shelf" />
                </div>
            @endforeach
        </div>
    </div>
</section>
