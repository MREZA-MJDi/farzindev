@props([
'product' => null,
'images' => null,
'variantImageMap' => [],
])

@php
    /*
    |--------------------------------------------------------------------------
    | Resolve Product
    |--------------------------------------------------------------------------
    */

    $resolvedProduct = $product;

    /*
    |--------------------------------------------------------------------------
    | Resolve Images
    |--------------------------------------------------------------------------
    */

    $resolvedImages = $images;

    if ($resolvedImages === null && $resolvedProduct) {
        $resolvedImages = $resolvedProduct->images;
    }

    $resolvedImages = collect($resolvedImages ?? []);

    /*
    |--------------------------------------------------------------------------
    | Product Name
    |--------------------------------------------------------------------------
    */

    $productName =
        $resolvedProduct?->name
        ?? 'محصول فرزین';

    /*
    |--------------------------------------------------------------------------
    | Product SKU
    |--------------------------------------------------------------------------
    */

    $productSku =
        $resolvedProduct?->sku
        ?? null;

    $imageCount =
        $resolvedImages->count();
@endphp


<div
    x-data="{
        active: 0,
        zoom: false,
        selectedVariantId: null,
        touchStartX: 0,
        touchStartY: 0,
        swiped: false,
        variantImageMap: @js($variantImageMap),

        images: @js(
            $resolvedImages->map(function ($image) use ($productName) {
                return [
                    'url' => $image->url,
                    'srcset' => $image->responsive_srcset,
                    'alt' => $productName,
                ];
            })->values()
        ),

        next() {
            if (!this.images.length) {
                return;
            }

            this.active =
                (this.active + 1) % this.images.length;
        },

        previous() {
            if (!this.images.length) {
                return;
            }

            this.active =
                (this.active - 1 + this.images.length)
                % this.images.length;
        },

        startTouch(event) {
            this.swiped = false;
            this.touchStartX = Number(event.changedTouches?.[0]?.screenX ?? 0);
            this.touchStartY = Number(event.changedTouches?.[0]?.screenY ?? 0);
        },

        endTouch(event) {
            const touch = event.changedTouches?.[0];
            if (!touch) return;

            const deltaX = Number(touch.screenX) - this.touchStartX;
            const deltaY = Number(touch.screenY) - this.touchStartY;

            if (Math.abs(deltaX) < 48 || Math.abs(deltaX) <= Math.abs(deltaY)) {
                return;
            }

            this.swiped = true;
            if (deltaX < 0) {
                this.next();
            } else {
                this.previous();
            }
        },

        selectVariant(event) {
            const detail = event?.detail ?? {};
            const id = detail.id ?? null;

            if (!id) {
                return;
            }

            this.selectedVariantId = String(id);

            const indexes =
                this.variantImageMap[String(id)]
                ?? this.variantImageMap[id]
                ?? [];

            if (indexes.length) {
                this.active = Number(indexes[0]) || 0;
            } else if (
                ['color', 'colour', 'رنگ'].includes(
                    String(detail.type ?? '').toLowerCase()
                )
            ) {
                this.active = 0;
            }
        },

        variantImageIndexes() {
            if (!this.selectedVariantId) {
                return [];
            }

            const indexes =
                this.variantImageMap[String(this.selectedVariantId)]
                ?? this.variantImageMap[this.selectedVariantId]
                ?? [];

            return indexes
                .map(index => Number(index))
                .filter(index => this.images[index]);
        }
    }"
    @product-variant-selected.window="selectVariant($event)"
    class="farzin-product-gallery w-full"
>


    {{-- =========================================================
         MAIN IMAGE
    ========================================================== --}}

    <div class="product-gallery-shell group relative overflow-hidden rounded-[2rem] bg-[var(--livora-surface)]">

        @if($imageCount > 0)

            <div
                class="product-gallery-stage relative aspect-square w-full cursor-zoom-in overflow-hidden sm:aspect-[4/5] lg:aspect-auto lg:h-[clamp(500px,calc(100svh-220px),720px)]"
                role="button"
                tabindex="0"
                aria-label="نمایش بزرگ‌نمایی تصویر محصول"
                @click="if (!swiped) zoom = true; swiped = false"
                @keydown.enter.prevent="zoom = true"
                @keydown.space.prevent="zoom = true"
                @keydown.arrow-left.prevent="next()"
                @keydown.arrow-right.prevent="previous()"
                @touchstart.passive="startTouch($event)"
                @touchend.passive="endTouch($event)"
            >

                {{-- Images --}}
                @foreach($resolvedImages as $index => $image)

                    <img
                        x-show="active === {{ $index }}"
                        x-transition:enter="transition duration-500"
                        x-transition:enter-start="opacity-0 scale-[1.02]"
                        x-transition:enter-end="opacity-100 scale-100"
                        src="{{ $image->url }}"
                        @if($image->responsive_srcset) srcset="{{ $image->responsive_srcset }}" sizes="(max-width: 1023px) 100vw, 50vw" @endif
                        alt="{{ $productName }}"
                        @if($index !== 0)
                        loading="lazy"
                        @endif
                        class="absolute inset-0 h-full w-full object-contain p-3 sm:p-5"
                    >

                @endforeach


                {{-- Counter --}}
                @if($imageCount > 1)

                    <div class="absolute right-4 top-4 z-10">

                        <span class="farzin-gallery-counter rounded-full border px-3 py-1.5 text-[10px] text-white backdrop-blur-xl">

                            <span x-text="active + 1"></span>

                            /

                            {{ \App\Helpers\PersianNumberHelper::format($imageCount) }}

                        </span>

                    </div>

                @endif


                {{-- Previous --}}
                @if($imageCount > 1)

                    <button
                        type="button"
                        aria-label="تصویر قبلی"
                        @click.stop="previous()"
                        class="farzin-gallery-arrow absolute right-4 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border text-white shadow-sm backdrop-blur-xl transition sm:opacity-100"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-4 w-4"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m15.75 19.5-7.5-7.5 7.5-7.5"
                            />

                        </svg>

                    </button>


                    {{-- Next --}}
                    <button
                        type="button"
                        aria-label="تصویر بعدی"
                        @click.stop="next()"
                        class="farzin-gallery-arrow absolute left-4 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border text-white shadow-sm backdrop-blur-xl transition sm:opacity-100"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-4 w-4"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m8.25 4.5 7.5 7.5-7.5 7.5"
                            />

                        </svg>

                    </button>

                @endif


                {{-- Gradient --}}
                <div class="farzin-gallery-gradient pointer-events-none absolute inset-x-0 bottom-0 h-32"></div>


                {{-- Zoom --}}
                <div class="farzin-gallery-zoom absolute bottom-4 right-4 z-10 flex items-center gap-2 rounded-full border px-3 py-2 text-[10px] text-white backdrop-blur-xl">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-3.5 w-3.5"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m15 15 4.5 4.5m-3-7.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm-4.5-2v4m-2-2h4"
                        />

                    </svg>

                    بزرگ‌نمایی

                </div>

            </div>

        @else

            {{-- No image --}}
            <div class="flex aspect-square w-full items-center justify-center sm:aspect-[4/5] lg:aspect-auto lg:h-[clamp(500px,calc(100svh-220px),720px)]">

                <div class="text-center">

                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)]">

                        <span class="text-xs tracking-[0.2em] text-[var(--livora-stone)]">
                            FZ
                        </span>

                    </div>

                    <p class="mt-4 text-xs text-[var(--livora-stone)]">
                        تصویر محصول موجود نیست
                    </p>

                </div>

            </div>

        @endif

    </div>


    {{-- =========================================================
         THUMBNAILS
    ========================================================== --}}

    @if($imageCount > 1)

        <div class="mt-4 flex gap-3 overflow-x-auto pb-1">

            @foreach($resolvedImages as $index => $image)

                <button
                    type="button"
                    @click="active = {{ $index }}"
                    :aria-current="active === {{ $index }} ? 'true' : 'false'"
                    aria-label="نمایش تصویر {{ $index + 1 }}"
                    class="group relative h-20 w-16 shrink-0 overflow-hidden rounded-2xl bg-[var(--livora-surface)] transition sm:h-24 sm:w-20"
                    :class="
                        active === {{ $index }}
                        ? 'ring-2 ring-[var(--livora-accent)] ring-offset-2 ring-offset-[var(--livora-cream)]'
                        : 'opacity-60 hover:opacity-100'
"
                >

                    <img
                        src="{{ $image->url }}"
                        @if($image->responsive_srcset) srcset="{{ $image->responsive_srcset }}" sizes="80px" @endif
                        alt="{{ ($image->alt ?: $productName) }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    >

                </button>

            @endforeach

        </div>

    @endif


    {{-- =========================================================
         SELECTED VARIANT IMAGES
    ========================================================== --}}

    <template x-if="variantImageIndexes().length">

        <div class="mt-5 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4">

            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                        تصاویر گزینه انتخاب‌شده
                    </p>

                    <p class="mt-1 text-xs text-[var(--livora-stone)]">
                        تصاویر مرتبط با گزینه انتخاب‌شده
                    </p>
                </div>

            </div>

            <div class="mt-3 flex gap-3 overflow-x-auto pb-1">

                <template
                    x-for="(imageIndex, index) in variantImageIndexes()"
                    :key="'variant-' + selectedVariantId + '-' + imageIndex"
                >

                    <button
                        type="button"
                        @click="active = imageIndex"
                        class="group h-16 w-14 shrink-0 overflow-hidden rounded-xl bg-[var(--livora-white)]"
                        :class="
                            active === imageIndex
                            ? 'ring-2 ring-[var(--livora-ink)] ring-offset-1'
                            : 'opacity-70 hover:opacity-100'
                        "
                        :aria-label="'نمایش تصویر ' + (index + 1) + ' از گزینه انتخاب‌شده'"
                    >

                        <img
                            :src="images[imageIndex].url"
                            :srcset="images[imageIndex].srcset"
                            sizes="80px"
                            :alt="images[imageIndex].alt"
                            loading="lazy"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                        >

                    </button>

                </template>

            </div>

        </div>

    </template>


    {{-- =========================================================
         META
    ========================================================== --}}

    <div class="mt-4 flex items-center justify-between gap-4">

        <div>

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                PRODUCT GALLERY
            </p>

            <p class="mt-1 text-xs text-[var(--livora-stone)]">

                @if($imageCount > 0)

                    {{ \App\Helpers\PersianNumberHelper::format($imageCount) }}
                    تصویر

                @else

                    بدون تصویر

                @endif

            </p>

        </div>


        @if($productSku)

            <span class="text-[10px] text-[var(--livora-stone)]">
                SKU: {{ $productSku }}
            </span>

        @endif

    </div>


    {{-- =========================================================
         LIGHTBOX
    ========================================================== --}}

    <template x-teleport="body">

        <div
            x-show="zoom"
            x-cloak
            x-transition.opacity
            class="farzin-gallery-lightbox fixed inset-0 z-[100] p-4 backdrop-blur-md sm:p-8"
            @keydown.escape.window="zoom = false"
        >

            {{-- Close --}}
            <button
                type="button"
                aria-label="بستن تصویر"
                @click="zoom = false"
                class="farzin-gallery-lightbox-close absolute right-4 top-4 z-20 flex h-11 w-11 items-center justify-center rounded-full border text-white backdrop-blur-xl transition sm:right-7 sm:top-7"
            >

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18 18 6M6 6l12 12"
                    />

                </svg>

            </button>


            {{-- Image --}}
            <div class="flex h-full min-h-0 items-center justify-center">

                <div class="relative max-h-full max-w-6xl">

                    <template
                        x-for="(image, index) in images"
                        :key="index"
                    >

                        <img
                            x-show="active === index"
                            x-transition.opacity
                            :src="image.url"
                            :srcset="image.srcset"
                            sizes="(max-width: 1023px) 100vw, 72vw"
                            :alt="image.alt"
                            class="max-h-[88vh] max-w-full rounded-2xl object-contain shadow-2xl"
                        >

                    </template>


                    @if($imageCount > 1)

                        <button
                            type="button"
                            aria-label="تصویر قبلی"
                            @click="previous()"
                            class="farzin-gallery-lightbox-arrow absolute right-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border text-white backdrop-blur-xl transition sm:right-5"
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-5 w-5"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m15.75 19.5-7.5-7.5 7.5-7.5"
                                />

                            </svg>

                        </button>


                        <button
                            type="button"
                            aria-label="تصویر بعدی"
                            @click="next()"
                            class="farzin-gallery-lightbox-arrow absolute left-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border text-white backdrop-blur-xl transition sm:left-5"
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-5 w-5"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m8.25 4.5 7.5 7.5-7.5 7.5"
                                />

                            </svg>

                        </button>

                    @endif

                </div>

            </div>

        </div>

    </template>

</div>
