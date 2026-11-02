@extends('layouts.app')

@php
    $pageTitle = $product->meta_title ?: ($product->name . ' | فرزین');
    $pageDescription = $product->meta_description
        ?: ($safeShortDescription ?: $safeDescription ?: $product->name);
    $pageCanonical = $product->canonical_url ?: route('product.show', $product->slug);
    $mainImage = $product->images->first(fn ($image) => filled($image->url));

    $productPrice = (float) $product->price;
    $phone = \App\Support\StorefrontContact::phone($contactSetting?->phone);
    $whatsappDigits = \App\Support\StorefrontContact::whatsapp($contactSetting?->whatsapp);

    $stockRequestMessage = 'سلام، لطفاً درباره زمان تأمین موجودی محصول «' . $product->name . '» راهنمایی‌ام کنید.';
    $consultationMessage = 'سلام، برای انتخاب و بررسی محصول «' . $product->name . '» به مشاوره نیاز دارم.';
    $stockRequestUrl = route('contact', [
        'subject' => 'product',
        'message' => $stockRequestMessage,
    ]) . '#contact-form';
    $consultationUrl = route('contact', [
        'subject' => 'product',
        'message' => $consultationMessage,
    ]) . '#contact-form';

    $whatsappUrl = $whatsappDigits
        ? 'https://wa.me/' . $whatsappDigits . '?' . http_build_query(['text' => $consultationMessage])
        : null;

    $hasShippingFlatRate = array_key_exists('shipping_flat_rate', $shippingSettings)
        && is_numeric($shippingSettings['shipping_flat_rate']);
    $hasFreeShippingThreshold = array_key_exists('shipping_free_threshold', $shippingSettings)
        && is_numeric($shippingSettings['shipping_free_threshold'])
        && (float) $shippingSettings['shipping_free_threshold'] > 0;
    $shippingFlatRate = $hasShippingFlatRate ? (float) $shippingSettings['shipping_flat_rate'] : null;
    $shippingFreeThreshold = $hasFreeShippingThreshold ? (float) $shippingSettings['shipping_free_threshold'] : null;
    $hasShippingInfo = $hasShippingFlatRate || $hasFreeShippingThreshold;

    $productTabs = [];
    if ($safeDescription || $safeShortDescription) {
        $productTabs[] = 'description';
    }
    if ($installmentPlan) {
        $productTabs[] = 'installment';
    }
    if ($hasShippingInfo) {
        $productTabs[] = 'shipping';
    }

    $galleryImageIndexes = $product->images
        ->values()
        ->mapWithKeys(fn ($image, $index) => [(int) $image->id => (int) $index])
        ->all();

    $variantImageMap = $product->variants
        ->mapWithKeys(fn ($variant) => [
            (string) $variant->id => $variant->images
                ->map(fn ($image) => $galleryImageIndexes[$image->id] ?? null)
                ->filter(fn ($index) => $index !== null)
                ->values()
                ->all(),
        ])
        ->all();

    $availability = $canPurchase
        ? 'https://schema.org/InStock'
        : 'https://schema.org/OutOfStock';
@endphp

@section('title', $pageTitle)
@section('description', $pageDescription)
@section('canonical', $pageCanonical)
@if(($product->seo_robots ?? 'inherit') !== 'inherit')
    @section('robots', $product->seo_robots)
@endif
@section('og_type', 'product')
@section('og_title', $product->og_title ?: $pageTitle)
@section('og_description', $product->og_description ?: $pageDescription)
@section('twitter_card', $mainImage ? 'summary_large_image' : 'summary')
@section('og_image', $mainImage?->url ?? '')

@push('seo')
    @if($mainImage)
        <meta property="og:image:alt" content="{{ $product->name }}">
    @endif
    <meta property="product:price:amount" content="{{ (int) round($productPrice * 10) }}">
    <meta property="product:price:currency" content="IRR">
    @php
        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $pageDescription,
            'url' => $pageCanonical,
            'brand' => ['@type' => 'Brand', 'name' => 'فرزین'],
            'offers' => [
                '@type' => 'Offer',
                'url' => $pageCanonical,
                'priceCurrency' => 'IRR',
                'price' => (string) (int) round($productPrice * 10),
                'availability' => $availability,
            ],
        ];

        if (filled($product->sku)) {
            $productSchema['sku'] = $product->sku;
        }
        if ($mainImage) {
            $productSchema['image'] = [$mainImage->url];
        }
        if ($product->category) {
            $productSchema['category'] = $product->category->name;
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($productSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@push('styles')
<style>
    .fz-product-layout{display:grid;grid-template-columns:minmax(0,1fr);align-items:start;gap:1rem}
    .fz-product-panel{min-width:0}
    .fz-product-panel__title{font-size:clamp(1.05rem,2vw,1.85rem);line-height:1.65}
    .fz-product-action{display:inline-flex;min-height:44px;align-items:center;justify-content:center;gap:.5rem;border-radius:1rem;padding:.75rem 1rem;font-size:.82rem;font-weight:700;text-align:center}
    .fz-product-accordion summary{min-height:52px;display:flex;align-items:center;justify-content:space-between;gap:1rem;cursor:pointer;list-style:none}
    .fz-product-accordion summary::-webkit-details-marker{display:none}
    .fz-product-accordion summary:focus-visible,.fz-product-action:focus-visible{outline:2px solid var(--livora-accent);outline-offset:3px}
    .fz-product-accordion[open] summary svg{transform:rotate(180deg)}
    .fz-product-sticky{padding-bottom:max(.75rem,env(safe-area-inset-bottom))}
    @media(max-width:767px){
        .fz-product-layout{grid-template-columns:minmax(0,.88fr) minmax(0,1.12fr);gap:.7rem}
        .fz-product-gallery .product-gallery-shell{border-radius:1rem}
        .fz-product-gallery .product-gallery-stage{aspect-ratio:1/1;max-height:none;height:auto}
        .fz-product-gallery .product-gallery-stage img{padding:.35rem}
        .fz-product-gallery .farzin-gallery-arrow{width:2.75rem;height:2.75rem}
        .fz-product-gallery .farzin-gallery-zoom{right:.4rem;bottom:.4rem;padding:.35rem .5rem;font-size:9px}
        .fz-product-gallery .mt-4.flex.gap-3{gap:.4rem;margin-top:.5rem}
        .fz-product-gallery .mt-4.flex.gap-3 button{width:2.65rem;height:2.65rem;border-radius:.6rem}
        .fz-product-panel__title{font-size:clamp(.95rem,4.4vw,1.15rem);line-height:1.55}
        .fz-product-panel .product-price__current strong{font-size:1.15rem}
        .fz-product-panel .product-price__current span{font-size:.65rem}
        .fz-product-panel .product-price__main{gap:.25rem}
        .fz-product-panel__status{font-size:10px;padding:.3rem .55rem;min-height:44px}
        .fz-product-panel .fz-product-action{width:100%;padding:.65rem .5rem;font-size:.72rem}
        .fz-product-panel .farzin-quantity{max-width:100%}
        .fz-product-panel .farzin-quantity__button{width:44px;height:44px}
        .fz-product-panel .farzin-quantity__input{width:2.5rem;min-height:44px}
        .fz-product-sku{font-size:9px}
    }
     .fz-product-layout--no-gallery{grid-template-columns:minmax(0,1fr)}
    @media(min-width:768px){
        .fz-product-layout{grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:clamp(1.5rem,4vw,3.5rem)}
    }
    @media(min-width:1024px){.fz-product-layout{grid-template-columns:minmax(0,1.05fr) minmax(0,.95fr)}}
    @media(max-width:360px){.fz-product-panel__title{font-size:.9rem}.fz-product-gallery .farzin-gallery-arrow{width:1.75rem;height:1.75rem}}
</style>
@endpush

@section('content')
<div class="bg-[var(--livora-cream)] pb-28 lg:pb-0">
    <x-layout.container>
        <nav aria-label="مسیر صفحه" class="flex min-w-0 items-center gap-2 overflow-hidden py-3 text-[11px] text-[var(--livora-stone)]">
            <a class="shrink-0 rounded py-1 hover:text-[var(--livora-text)]" href="{{ route('home') }}">خانه</a>
            <span aria-hidden="true">/</span>
            <a class="shrink-0 rounded py-1 hover:text-[var(--livora-text)]" href="{{ route('shop.index') }}">فروشگاه</a>
            @if($product->category)
                <span aria-hidden="true">/</span>
                <a class="min-w-0 truncate rounded py-1 hover:text-[var(--livora-text)]" href="{{ route('categories.show', $product->category->slug) }}">{{ $product->category->name }}</a>
            @endif
        </nav>

        <section class="fz-product-layout py-2 sm:py-6 {{ $mainImage ? '' : 'fz-product-layout--no-gallery' }}" aria-labelledby="product-title">
            @if($mainImage)
                <div class="min-w-0">
                    <x-product.gallery :product="$product" :variant-image-map="$variantImageMap" />
                </div>
            @endif

            <div class="fz-product-panel min-w-0 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-3 sm:rounded-3xl sm:p-6 lg:p-8">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="fz-product-panel__status inline-flex min-h-8 items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $canPurchase ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-[var(--livora-border)] bg-[var(--livora-surface)] text-[var(--livora-stone)]' }}">
                        {{ $canPurchase ? 'موجود' : 'ناموجود' }}
                    </span>
                    @if($product->is_new)
                        <span class="inline-flex min-h-8 items-center rounded-full bg-[var(--livora-surface)] px-3 py-1 text-[11px] text-[var(--livora-text)]">جدید</span>
                    @endif
                </div>

                @if($product->category)
                    <p class="mt-3 text-[11px] font-medium text-[var(--livora-accent)]">{{ $product->category->name }}</p>
                @endif

                <h1 id="product-title" class="fz-product-panel__title mt-2 font-bold tracking-tight text-[var(--livora-text)]">{{ $product->name }}</h1>

                <div class="mt-3">
                    <x-product.price :product="$product" :show-installment="false" />
                </div>

                @if($installmentPlan)
                    <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] leading-6 text-[var(--livora-stone)]">
                        <span>خرید اقساطی؛ پیش‌پرداخت {{ \App\Helpers\PersianNumberHelper::format($installmentPlan['cash_percent']) }}٪</span>
                        <a href="#product-installment" class="font-semibold text-[var(--livora-accent)] underline underline-offset-4">جزئیات</a>
                    </p>
                @endif

                @if($safeShortDescription)
                    <p class="mt-3 hidden text-xs leading-7 text-[var(--livora-stone)] sm:block">{{ $safeShortDescription }}</p>
                @endif

                @if($canPurchase)
                    <form id="product-purchase" action="{{ route('cart.add', $product) }}" method="POST" data-product-cart-form class="mt-4 space-y-3">
                        @csrf
                        @if($variantGroups->isNotEmpty())
                            <div class="space-y-3 border-t border-[var(--livora-border)] pt-3">
                                @foreach($variantGroups as $type => $variants)
                                    <x-product.variant-selector
                                        :title="$variants->first()->name ?: 'انتخاب ویژگی'"
                                        :options="$variants"
                                        :name="$type"
                                        :type="$type"
                                    />
                                @endforeach
                                @if($errors->has('variants') || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'variants.')))
                                    <p class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs leading-6 text-red-800" role="alert">لطفاً ویژگی‌های لازم محصول را انتخاب کنید.</p>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center justify-between gap-3 border-t border-[var(--livora-border)] pt-3">
                            <span class="text-xs font-medium text-[var(--livora-text)]">تعداد</span>
                            <x-shop.quantity name="quantity" min="1" :max="$maxPurchaseQuantity" />
                        </div>

                        <button type="submit" class="fz-product-action w-full bg-[var(--livora-ink)] text-white transition hover:bg-[var(--livora-accent)]">
                            افزودن به سبد خرید
                        </button>
                    </form>
                @else
                    <div class="mt-4 space-y-2 border-t border-[var(--livora-border)] pt-4">
                        <a class="fz-product-action w-full bg-[var(--livora-ink)] text-white hover:bg-[var(--livora-accent)]" href="{{ $stockRequestUrl }}">درخواست اطلاع از موجودی</a>
                        @if($phone || $whatsappUrl)
                            <div class="grid grid-cols-1 gap-2 {{ $phone && $whatsappUrl ? 'sm:grid-cols-2' : '' }}">
                                @if($phone)
                                    <a class="fz-product-action border border-[var(--livora-border)] text-[var(--livora-text)]" href="{{ $phone['href'] }}">مشاوره تلفنی</a>
                                @endif
                                @if($whatsappUrl)
                                    <a class="fz-product-action border border-[var(--livora-border)] text-[var(--livora-text)]" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">مشاوره در واتساپ</a>
                                @endif
                            </div>
                        @endif
                        <p class="text-[10px] leading-5 text-[var(--livora-stone)]">درخواست اطلاع از موجودی از طریق فرم تماس برای پیگیری تیم فروش ثبت می‌شود.</p>
                    </div>
                @endif

                @if(filled($product->sku))
                    <p class="fz-product-sku mt-4 border-t border-[var(--livora-border)] pt-3 text-[10px] text-[var(--livora-stone)]" dir="ltr">شناسه محصول: {{ $product->sku }}</p>
                @endif
            </div>
        </section>

        @if(count($productTabs))
            <section class="my-8 space-y-3 sm:my-12" aria-label="اطلاعات تکمیلی محصول">
                @foreach($productTabs as $index => $tab)
                    <details @if($index === 0) open @endif @if($tab === 'installment') id="product-installment" @endif class="fz-product-accordion rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 sm:px-6">
                        <summary class="text-sm font-semibold text-[var(--livora-text)]">
                            <span>
                                @if($tab === 'description') توضیحات محصول
                                @elseif($tab === 'installment') شرایط خرید اقساطی
                                @elseif($tab === 'shipping') ارسال
                                @endif
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-[var(--livora-stone)] transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </summary>

                        @if($tab === 'description')
                            @php
                                $descriptionText = $safeDescription ?: $safeShortDescription;
                                $descriptionPreview = \Illuminate\Support\Str::limit($descriptionText, 420);
                                $hasMoreDescription = mb_strlen($descriptionText) > 420;
                            @endphp
                            <div class="border-t border-[var(--livora-border)] py-4 text-sm leading-8 text-[var(--livora-stone)]" x-data="{ expanded: false }">
                                <div x-show="!expanded">{{ nl2br(e($descriptionPreview)) }}</div>
                                @if($hasMoreDescription)
                                    <div x-show="expanded" x-cloak>{{ nl2br(e($descriptionText)) }}</div>
                                    <button type="button" class="mt-3 inline-flex min-h-11 items-center rounded-lg font-semibold text-[var(--livora-accent)] underline underline-offset-4" @click="expanded = !expanded" :aria-expanded="expanded.toString()">
                                        <span x-show="!expanded">ادامه مطلب</span>
                                        <span x-show="expanded" x-cloak>نمایش کمتر</span>
                                    </button>
                                @endif
                            </div>
                        @elseif($tab === 'installment')
                            <div class="grid gap-3 border-t border-[var(--livora-border)] py-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="rounded-xl bg-[var(--livora-surface)] p-3">
                                    <p class="text-[11px] text-[var(--livora-stone)]">پیش‌پرداخت</p>
                                    <p class="mt-2 text-sm font-bold">{{ \App\Helpers\PersianNumberHelper::format($installmentPlan['cash_amount']) }} تومان</p>
                                </div>
                                <div class="rounded-xl bg-[var(--livora-surface)] p-3">
                                    <p class="text-[11px] text-[var(--livora-stone)]">مبلغ باقی‌مانده</p>
                                    <p class="mt-2 text-sm font-bold">{{ \App\Helpers\PersianNumberHelper::format($installmentPlan['deferred_amount']) }} تومان</p>
                                </div>
                                <div class="rounded-xl bg-[var(--livora-surface)] p-3">
                                    <p class="text-[11px] text-[var(--livora-stone)]">تعداد چک</p>
                                    <p class="mt-2 text-sm font-bold">{{ \App\Helpers\PersianNumberHelper::format($installmentPlan['cheque_count']) }} فقره</p>
                                </div>
                                <div class="rounded-xl bg-[var(--livora-surface)] p-3">
                                    <p class="text-[11px] text-[var(--livora-stone)]">فاصله سررسید</p>
                                    <p class="mt-2 text-sm font-bold">هر {{ \App\Helpers\PersianNumberHelper::format($installmentPlan['interval_months']) }} ماه</p>
                                </div>
                                <div class="sm:col-span-2 lg:col-span-4">
                                    @if($installmentPlan['without_fee'])
                                        <p class="text-xs font-semibold text-[var(--livora-accent)]">بدون کارمزد؛ جمع پیش‌پرداخت و اقساط با قیمت نقدی برابر است.</p>
                                    @endif
                                    <ol class="mt-3 grid gap-2 sm:grid-cols-2">
                                        @foreach($installmentPlan['cheque_amounts'] as $chequeIndex => $amount)
                                            <li class="flex items-center justify-between gap-3 rounded-xl border border-[var(--livora-border)] px-3 py-2 text-xs">
                                                <span>چک {{ \App\Helpers\PersianNumberHelper::format($chequeIndex + 1) }} · {{ \App\Helpers\PersianNumberHelper::format(($chequeIndex + 1) * $installmentPlan['interval_months']) }} ماه بعد</span>
                                                <strong>{{ \App\Helpers\PersianNumberHelper::format($amount) }} تومان</strong>
                                            </li>
                                        @endforeach
                                    </ol>
                                    <p class="mt-3 text-[11px] leading-6 text-[var(--livora-stone)]">برنامه پرداخت این محصول بر اساس شرایط ثبت‌شده در فروشگاه محاسبه شده است.</p>
                                </div>
                            </div>
                        @elseif($tab === 'shipping')
                            <div class="grid gap-3 border-t border-[var(--livora-border)] py-4 sm:grid-cols-2">
                                @if($hasShippingFlatRate)
                                    <div class="rounded-xl bg-[var(--livora-surface)] p-3">
                                        <p class="text-[11px] text-[var(--livora-stone)]">هزینه پایه ارسال</p>
                                        <p class="mt-2 text-sm font-bold">{{ $shippingFlatRate > 0 ? \App\Helpers\PersianNumberHelper::format($shippingFlatRate) . ' تومان' : 'رایگان' }}</p>
                                    </div>
                                @endif
                                @if($hasFreeShippingThreshold)
                                    <div class="rounded-xl bg-[var(--livora-surface)] p-3">
                                        <p class="text-[11px] text-[var(--livora-stone)]">حداقل مبلغ برای ارسال رایگان</p>
                                        <p class="mt-2 text-sm font-bold">{{ \App\Helpers\PersianNumberHelper::format($shippingFreeThreshold) }} تومان</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </details>
                @endforeach
            </section>
        @endif

        @if($relatedProducts->isNotEmpty())
            <section class="border-t border-[var(--livora-border)] py-8 sm:py-12" aria-labelledby="related-products-title">
                <div class="mb-5 flex items-end justify-between gap-3">
                    <div>
                        <h2 id="related-products-title" class="text-xl font-bold text-[var(--livora-text)]">مدل‌های مشابه موجود</h2>
                        @if($product->category)
                            <p class="mt-1 text-xs text-[var(--livora-stone)]">از دسته {{ $product->category->name }}</p>
                        @endif
                    </div>
                    @if($product->category)
                        <a class="inline-flex min-h-11 items-center rounded-lg px-2 text-xs font-semibold text-[var(--livora-accent)]" href="{{ route('categories.show', $product->category->slug) }}">همه مدل‌ها</a>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($relatedProducts as $relatedProduct)
                        <x-product.card :product="$relatedProduct" />
                    @endforeach
                </div>
            </section>
        @endif
    </x-layout.container>

    <div class="fz-product-sticky fixed inset-x-0 bottom-[5.5rem] z-[80] border-t border-[var(--livora-border)] bg-[var(--livora-white)]/95 p-3 shadow-[0_-10px_30px_rgba(24,23,21,.08)] backdrop-blur-xl lg:hidden">
        <x-layout.container>
            <div class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[10px] text-[var(--livora-stone)]">{{ $product->name }}</p>
                    <p class="mt-1 text-sm font-bold">{{ \App\Helpers\PersianNumberHelper::format($productPrice) }} <span class="text-[10px] font-normal text-[var(--livora-stone)]">تومان</span></p>
                </div>
                @if($canPurchase)
                    <a href="#product-purchase" class="fz-product-action shrink-0 bg-[var(--livora-ink)] text-white">افزودن به سبد</a>
                @else
                    <a href="{{ $stockRequestUrl }}" class="fz-product-action shrink-0 bg-[var(--livora-ink)] text-white">درخواست موجودی</a>
                    @if($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="مشاوره در واتساپ" class="fz-product-action shrink-0 border border-[var(--livora-border)] text-[var(--livora-text)]">مشاوره</a>
                    @elseif($phone)
                        <a href="{{ $phone['href'] }}" aria-label="مشاوره تلفنی" class="fz-product-action shrink-0 border border-[var(--livora-border)] text-[var(--livora-text)]">مشاوره</a>
                    @endif
                @endif
            </div>
        </x-layout.container>
    </div>
</div>
@endsection
