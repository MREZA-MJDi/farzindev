@extends('layouts.app')

@php
    $pageTitle = $product->meta_title ?: ($product->name . ' | فرزین');
    $pageDescription = $product->meta_description
        ?: ($safeShortDescription ?: $safeDescription ?: $product->name);
    $pageCanonical = $product->canonical_url ?: route('product.show', $product->slug);
    $mainImage = $product->primaryImage();

    $productPrice = (float) $product->price;
    $productPriceMin = (float) ($productPriceRange['min'] ?? $productPrice);
    $productPriceMax = (float) ($productPriceRange['max'] ?? $productPrice);
    $hasProductPriceRange = $productPriceMax - $productPriceMin >= 0.01;
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

    $specificationLabels = [
        'dimensions' => 'ابعاد کلی', 'length' => 'طول', 'width' => 'عرض', 'height' => 'ارتفاع',
        'installation' => 'نحوه نصب', 'basin_count' => 'تعداد لگن', 'material' => 'جنس بدنه',
        'basin_depth' => 'عمق لگن', 'sheet_thickness' => 'ضخامت ورق', 'capacity_liters' => 'ظرفیت لگن',
        'weight' => 'وزن', 'basin_direction' => 'جهت لگن', 'design_type' => 'نوع طراحی',
        'drainboard' => 'سینی', 'tap_holes' => 'تعداد و محل سوراخ شیر', 'light_count' => 'تعداد لامپ',
        'switch_type' => 'نوع کلید', 'hood_type' => 'نوع هود', 'hood_model' => 'مدل',
        'suction_capacity' => 'قدرت مکش', 'filter_type' => 'نوع فیلتر', 'motor_count' => 'تعداد موتور',
        'noise_level' => 'میزان صدا', 'air_outlet' => 'نوع خروجی هوا', 'burner_count' => 'تعداد شعله',
        'size_range' => 'محدوده سایز', 'burner_position' => 'محل شعله پلوپز', 'surface_material' => 'جنس صفحه',
        'glass_type' => 'نوع شیشه', 'glass_thickness' => 'ضخامت شیشه', 'grate_material' => 'جنس شبکه',
        'knob_type' => 'نوع ولوم', 'knob_material' => 'جنس ولوم', 'ignition_type' => 'نوع فندک',
        'burner_type' => 'نوع سرشعله', 'valve_type' => 'نوع شیر', 'safety_system' => 'سیستم ایمنی و ترموکوپل',
        'energy_rating' => 'رده مصرف انرژی', 'energy_source' => 'منبع انرژی', 'power_cable' => 'کابل برق',
        'accessories' => 'لوازم همراه', 'model_code' => 'مدل کالا / کد محصول',
    ];
    $storedSpecifications = collect($product->specifications ?? []);
    $categoryIdentifier = mb_strtolower(($product->category?->slug ?? '').' '.($product->category?->name ?? ''));
    $legacySpecificationGroups = [
        'sink' => ['dimensions', 'length', 'width', 'height', 'installation', 'basin_count', 'material', 'basin_depth', 'sheet_thickness', 'capacity_liters', 'weight', 'basin_direction', 'design_type', 'drainboard', 'tap_holes', 'accessories'],
        'hood' => ['dimensions', 'length', 'width', 'height', 'hood_type', 'hood_model', 'light_count', 'switch_type', 'material', 'suction_capacity', 'filter_type', 'motor_count', 'noise_level', 'air_outlet', 'accessories'],
        'gas' => ['dimensions', 'length', 'width', 'height', 'burner_count', 'size_range', 'burner_position', 'surface_material', 'material', 'glass_type', 'glass_thickness', 'grate_material', 'knob_type', 'knob_material', 'ignition_type', 'burner_type', 'valve_type', 'safety_system', 'energy_rating', 'energy_source', 'power_cable', 'accessories', 'model_code'],
    ];
    $categorySpecificationType = str_contains($categoryIdentifier, 'sink') || str_contains($categoryIdentifier, 'سینک')
        ? 'sink'
        : ((str_contains($categoryIdentifier, 'hood') || str_contains($categoryIdentifier, 'هود'))
            ? 'hood'
            : ((str_contains($categoryIdentifier, 'gas') || str_contains($categoryIdentifier, 'اجاق') || str_contains($categoryIdentifier, 'گاز'))
                ? 'gas' : null));

    if ($storedSpecifications->isNotEmpty() && array_is_list($storedSpecifications->all())
        && collect($storedSpecifications->first())->has('label')) {
        $visibleSpecifications = $storedSpecifications
            ->filter(fn ($row) => is_array($row)
                && filled(trim((string) ($row['label'] ?? '')))
                && is_scalar($row['value'] ?? null)
                && filled(trim((string) $row['value'])))
            ->map(fn ($row) => [
                'label' => trim((string) $row['label']),
                'value' => trim((string) $row['value']),
            ])->values();
    } else {
        $allowedLegacyKeys = $categorySpecificationType
            ? ($legacySpecificationGroups[$categorySpecificationType] ?? [])
            : null;
        $visibleSpecifications = $storedSpecifications
            ->filter(fn ($value, $key) => is_scalar($value)
                && filled(trim((string) $value))
                && ($allowedLegacyKeys === null || in_array((string) $key, $allowedLegacyKeys, true)))
            ->map(fn ($value, $key) => [
                'label' => $specificationLabels[$key] ?? str_replace('_', ' ', (string) $key),
                'value' => trim((string) $value),
            ])->values();
    }

    $productTabs = [];
    if ($visibleSpecifications->isNotEmpty()) {
        $productTabs[] = 'specifications';
    }
    if ($safeDescription) {
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
    <meta property="product:price:amount" content="{{ (int) round($productPriceMin * 10) }}">
    <meta property="product:price:currency" content="IRR">
    @php
        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $pageDescription,
            'url' => $pageCanonical,
            'brand' => ['@type' => 'Brand', 'name' => 'فرزین'],
            'offers' => $hasProductPriceRange
                ? [
                    '@type' => 'AggregateOffer',
                    'url' => $pageCanonical,
                    'priceCurrency' => 'IRR',
                    'lowPrice' => (string) (int) round($productPriceMin * 10),
                    'highPrice' => (string) (int) round($productPriceMax * 10),
                    'availability' => $availability,
                ]
                : [
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
    @vite('resources/css/product.css')
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
                    <x-product.price :product="$product" :show-installment="false" :price-range="$productPriceRange" />
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
                                @if($tab === 'specifications') مشخصات فنی
                                @elseif($tab === 'description') توضیحات محصول
                                @elseif($tab === 'installment') شرایط خرید اقساطی
                                @elseif($tab === 'shipping') ارسال
                                @endif
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-[var(--livora-stone)] transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </summary>

                        @if($tab === 'specifications')
                            <dl class="grid grid-cols-1 gap-0 border-t border-[var(--livora-border)] py-2 sm:grid-cols-2">
                                @foreach($visibleSpecifications as $specification)
                                    <div class="flex min-w-0 items-start justify-between gap-4 border-b border-[var(--livora-border)] px-1 py-3 text-sm">
                                        <dt class="shrink-0 text-[var(--livora-stone)]">{{ $specification['label'] }}</dt>
                                        <dd class="min-w-0 text-left font-semibold text-[var(--livora-text)]" dir="auto">{{ $specification['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @elseif($tab === 'description')
                            @php
                                $descriptionText = $safeDescription;
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
                        <x-product.card :product="$relatedProduct" :show-availability="false" />
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
