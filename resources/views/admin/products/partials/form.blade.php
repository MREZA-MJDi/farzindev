@php
    $editing = isset($product);
    $product = $product ?? new \App\Models\Product();

    $installmentEnabled = (bool) old(
        'installment_enabled',
        $product->installment_enabled ?? false
    );

    $cashPercent = old(
        'installment_cash_percent',
        $product->installment_cash_percent ?? 50
    );

    $remainderMethod = old(
        'installment_remainder_method',
        $product->installment_remainder_method ?? 'cheque'
    );

    $chequeCount = old(
        'installment_cheque_count',
        $product->installment_cheque_count ?? 2
    );

    $intervalMonths = old(
        'installment_interval_months',
        $product->installment_interval_months ?? 2
    );
@endphp

@csrf

<div class="grid grid-cols-1 gap-6 pb-28 xl:grid-cols-3">

    {{-- ============================================================
         Main Information
    ============================================================ --}}
    <div class="space-y-6 xl:col-span-2">

        @php
            $primaryImage = null;

            if ($editing) {
                $availableProductImages = $product->relationLoaded('images')
                    ? $product->images->filter(fn ($image) => filled($image->url))->values()
                    : $product->images()
                        ->orderByDesc('is_primary')
                        ->orderBy('sort_order')
                        ->get()
                        ->filter(fn ($image) => filled($image->url))
                        ->values();
                $primaryImage = $availableProductImages->firstWhere('is_primary', true)
                    ?? $availableProductImages->first();
            }

            $productImageUrl = $primaryImage?->url;
        @endphp

        {{-- Product Image --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    تصویر اصلی محصول
                </h3>

                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">
                    {{ $editing
                        ? 'تصویر اصلی را عوض کنید؛ تصاویر دیگر و اتصال Variantها حفظ می‌شوند.'
                        : 'برای محصول جدید یک تصویر اصلی انتخاب کنید. پس از ذخیره، در همین صفحه رنگ‌ها و تصاویر اختصاصی هر رنگ را تعریف کنید.' }}
                </p>
            </div>

            <div class="admin-image-preview mb-5 aspect-[4/3]">

                <img
                    id="product-image-preview"
                    data-image-editor-existing-preview
                    @if($productImageUrl) src="{{ $productImageUrl }}" @endif
                    alt="{{ $primaryImage?->alt ?: ($product->name ?? 'تصویر محصول') }}"
                    class="admin-image {{ $productImageUrl ? '' : 'hidden' }}"
                >

                <div
                    id="product-image-placeholder"
                    data-image-editor-placeholder
                    class="{{ $productImageUrl ? 'hidden' : 'flex' }} h-full flex-col items-center justify-center rounded-2xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-6 text-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="1.5"
                         stroke="currentColor"
                         class="mb-3 h-10 w-10 text-[var(--admin-muted)]">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8.25 8.25h.008v.008H8.25V8.25Z" />
                    </svg>

                    <span class="text-xs text-[var(--admin-muted)]">
                        {{ $editing ? 'تصویری برای این محصول ثبت نشده است.' : 'هنوز تصویری انتخاب نشده است.' }}
                    </span>
                </div>

            </div>

            <label for="image" class="admin-label">
                {{ $editing ? 'تغییر تصویر' : 'انتخاب تصویر اصلی' }}
            </label>
            <p class="mb-2 text-xs leading-6 text-[var(--admin-muted)]">
                برای انتشار محصول در سایت، تصویر اصلی و یک دسته‌بندی فعال الزامی است. پیش‌نویس را می‌توانید بدون تصویر ذخیره کنید.
            </p>

            <input
                id="image"
                name="image"
                type="file"
                data-image-editor-input
                data-image-editor-min-width="800"
                data-image-editor-min-height="600"
                accept="image/jpeg,image/png,image/webp"
                class="admin-input p-2"
            >

            @if(isset($mediaImages) && $mediaImages->isNotEmpty())
                <details class="mt-4 rounded-xl border border-[var(--admin-border)] p-4">
                    <summary class="cursor-pointer text-sm font-semibold text-[var(--admin-text)]">یا انتخاب تصویر اصلی از کتابخانه رسانه</summary>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">فقط یک تصویر اصلی انتخاب کنید. در این حالت فایل جدید بالا را انتخاب نکنید؛ تصویر کتابخانه بدون آپلود دوباره به محصول متصل می‌شود.</p>
                    <div class="mt-3 grid max-h-80 gap-3 overflow-y-auto sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($mediaImages as $mediaItem)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[var(--admin-border)] p-2 text-xs hover:bg-[var(--admin-surface-soft)]">
                                <input
                                    type="radio"
                                    name="primary_media_id"
                                    value="{{ $mediaItem->id }}"
                                    @checked((string) old('primary_media_id', $primaryImage?->media_id ?? '') === (string) $mediaItem->id)
                                    class="admin-checkbox h-4 w-4 shrink-0"
                                >
                                <span class="admin-media-choice-preview h-14 w-20 shrink-0 overflow-hidden rounded-lg">
                                    <img src="{{ $mediaItem->url }}" alt="{{ $mediaItem->alt ?: $mediaItem->original_name }}" loading="lazy" class="h-full w-full object-contain p-1">
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate font-medium">{{ $mediaItem->alt ?: $mediaItem->original_name }}</span>
                                    <span class="mt-1 block text-[10px] text-[var(--admin-muted)]">{{ $mediaItem->human_size }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('primary_media_id')
                        <p role="alert" class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </details>
                <script>
                    (() => {
                        const root = document.currentScript?.closest('.admin-card') || document;
                        const upload = root.querySelector('#image');
                        const mediaChoices = root.querySelectorAll('input[name="primary_media_id"]');

                        upload?.addEventListener('change', () => {
                            if (!upload.files?.length) return;
                            mediaChoices.forEach((choice) => { choice.checked = false; });
                        });

                        mediaChoices.forEach((choice) => {
                            choice.addEventListener('change', () => {
                                if (!choice.checked || !upload?.files?.length) return;
                                upload.value = '';
                                upload.dispatchEvent(new Event('change', { bubbles: true }));
                            });
                        });
                    })();
                </script>
            @endif

            <div data-image-editor-panel hidden class="mt-5 space-y-4 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                <strong class="block text-sm font-semibold text-[var(--admin-text)]">برش و ویرایش تصویر</strong>
                <canvas data-image-editor-canvas role="img" aria-label="پیش‌نمایش برش تصویر" class="mx-auto block max-w-full rounded-xl bg-[var(--admin-bg)]" style="width:min(100%,640px);height:auto;aspect-ratio:4/3;touch-action:none;cursor:grab;"></canvas>
                <p class="text-xs leading-6 text-[var(--admin-muted)]">تصویر را با ماوس یا لمس بکشید، نسبت کادر را انتخاب کنید و زوم یا چرخش را تنظیم کنید. پیش‌نمایش با خروجی آپلود هماهنگ است.</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <label class="block text-xs text-[var(--admin-text)]">نسبت کادر
                        <input type="hidden" data-image-editor-ratio value="4:3">
                    <p class="mt-2 rounded-lg border border-[var(--admin-border)] bg-[var(--admin-card)] px-3 py-2 text-xs leading-6 text-[var(--admin-muted)]">نسبت تصویر اصلی محصول ۴:۳ است؛ همین قاب در کارت فروشگاه و گالری جزئیات نمایش داده می‌شود. برای تنظیم دقیق، تصویر را جابه‌جا یا بزرگ‌نمایی کنید.</p>
                    </label>
                    <label class="block text-xs text-[var(--admin-text)]">بزرگ‌نمایی <span data-image-editor-zoom-label>۱×</span>
                        <input data-image-editor-zoom type="range" min="1" max="3" step="0.05" value="1" class="mt-3 w-full">
                    </label>
                    <div class="flex flex-wrap items-end gap-2"><button type="button" data-image-editor-rotate="-90" class="admin-btn admin-btn-secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M4 7v5h5" /><path d="M4.8 13.5A8 8 0 1 0 7 6.1L4 9" /></svg> چرخش به چپ</button><button type="button" data-image-editor-rotate="90" class="admin-btn admin-btn-secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M20 7v5h-5" /><path d="M19.2 13.5A8 8 0 1 1 17 6.1L20 9" /></svg> چرخش به راست</button><button type="button" data-image-editor-reset class="admin-btn admin-btn-ghost">بازنشانی</button></div>
                </div>
                <p data-image-editor-status class="text-xs text-[var(--admin-muted)]" aria-live="polite">کادر را تنظیم کنید.</p>
            </div>

            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                {{ $editing
                    ? 'با انتخاب فایل جدید، همین تصویر اصلی جایگزین می‌شود؛ تصاویر دیگر و اتصال Variantها باقی می‌مانند. حداکثر حجم تصویر ۲۰ مگابایت است.'
                    : 'تصویر اصلی محصول را انتخاب کنید. فرمت‌های مجاز JPG، PNG و WEBP؛ حداکثر حجم ۲۰ مگابایت.' }}
            </p>

            @error('image')
            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                {{ $message }}
            </p>
            @enderror

        </div>


        {{-- ========================================================
             Product Information
        ========================================================= --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات محصول
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    اطلاعات اصلی محصول را وارد کنید.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                {{-- Category --}}
                <div class="sm:col-span-2">

                    <label for="category_id" class="admin-label">
                        دسته‌بندی
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        class="admin-select"
                        required
                    >
                        <option value="">
                            انتخاب دسته‌بندی
                        </option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                data-slug="{{ $category->slug }}"
                                @selected(old('category_id', $product->category_id ?? '') == $category->id)
                            >
                            {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                @include('admin.products.partials.attribute-fields')

                {{-- Name --}}
                <div class="sm:col-span-2">

                    <label for="name" class="admin-label">
                        نام محصول
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $product->name ?? '') }}"
                        class="admin-input"
                        required
                    >

                    @error('name')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Slug --}}
                <div>

                    <label for="slug" class="admin-label">
                        Slug
                    </label>

                    <input
                        id="slug"
                        name="slug"
                        type="text"
                        value="{{ old('slug', $product->slug ?? '') }}"
                        dir="ltr"
                        class="admin-input text-left"
                        placeholder="product-slug"
                    >

                    <p class="mt-2 text-xs text-[var(--admin-muted)]">
                        در صورت خالی بودن، از نام محصول ساخته می‌شود.
                    </p>

                    @error('slug')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- SKU --}}
                <div>

                    <label for="sku" class="admin-label">
                        SKU
                    </label>

                    <input
                        id="sku"
                        name="sku"
                        type="text"
                        value="{{ old('sku', $product->sku ?? '') }}"
                        dir="ltr"
                        class="admin-input text-left"
                        required
                    >

                    @error('sku')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Short Description --}}
                <div class="sm:col-span-2">

                    <label for="short_description" class="admin-label">
                        توضیح کوتاه
                    </label>

                    <textarea
                        id="short_description"
                        name="short_description"
                        rows="3"
                        maxlength="255"
                        class="admin-textarea"
                    >{{ old('short_description', $product->short_description ?? '') }}</textarea>

                    @error('short_description')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Dynamic category-specific product specifications --}}
                @php
                    $specificationLabels = [
                        'dimensions' => 'ابعاد کلی', 'length' => 'طول', 'width' => 'عرض', 'height' => 'ارتفاع',
                        'installation' => 'نحوه نصب', 'basin_count' => 'تعداد لگن', 'material' => 'جنس بدنه',
                        'basin_depth' => 'عمق لگن', 'sheet_thickness' => 'ضخامت ورق', 'capacity_liters' => 'ظرفیت لگن',
                        'weight' => 'وزن', 'basin_direction' => 'جهت لگن', 'design_type' => 'نوع طراحی',
                        'drainboard' => 'سینی', 'tap_holes' => 'تعداد و محل سوراخ شیر', 'accessories' => 'لوازم همراه',
                        'hood_type' => 'نوع هود', 'model' => 'مدل', 'size' => 'سایز', 'light_count' => 'تعداد لامپ',
                        'switch_type' => 'نوع کلید', 'body_material' => 'جنس بدنه', 'suction_capacity' => 'قدرت مکش',
                        'filter_type' => 'نوع فیلتر', 'motor_count' => 'تعداد موتور', 'noise_level' => 'میزان صدا',
                        'air_outlet' => 'نوع خروجی هوا', 'burner_count' => 'تعداد شعله', 'size_range' => 'محدوده سایز',
                        'burner_position' => 'محل شعله پلوپز', 'surface_material' => 'جنس صفحه', 'glass_type' => 'نوع شیشه',
                        'glass_thickness' => 'ضخامت شیشه', 'grate_material' => 'جنس شبکه', 'knob_type' => 'نوع ولوم',
                        'knob_material' => 'جنس ولوم', 'ignition_type' => 'نوع فندک', 'burner_type' => 'نوع سرشعله',
                        'valve_type' => 'نوع شیر', 'safety_system' => 'سیستم ایمنی و ترموکوپل',
                        'energy_rating' => 'رده مصرف انرژی', 'energy_source' => 'منبع انرژی',
                        'power_cable' => 'کابل برق', 'model_code' => 'مدل کالا / کد محصول',
                    ];
                    $specificationTemplates = [
                        'sink' => [
                            ['label' => 'نحوه نصب', 'value' => ''], ['label' => 'ابعاد کلی', 'value' => ''],
                            ['label' => 'تعداد لگن', 'value' => ''], ['label' => 'جنس بدنه', 'value' => ''],
                            ['label' => 'عمق لگن', 'value' => ''], ['label' => 'ضخامت ورق', 'value' => ''],
                            ['label' => 'ظرفیت لگن', 'value' => ''], ['label' => 'وزن', 'value' => ''],
                            ['label' => 'جهت لگن', 'value' => ''], ['label' => 'نوع طراحی', 'value' => ''],
                            ['label' => 'سینی', 'value' => ''], ['label' => 'تعداد و محل سوراخ شیر', 'value' => ''],
                            ['label' => 'لوازم همراه', 'value' => ''],
                        ],
                        'hood' => [
                            ['label' => 'نوع هود', 'value' => ''], ['label' => 'مدل', 'value' => ''],
                            ['label' => 'سایز (سانتی‌متر)', 'value' => ''], ['label' => 'طول', 'value' => ''],
                            ['label' => 'عرض', 'value' => ''], ['label' => 'تعداد لامپ', 'value' => ''],
                            ['label' => 'نوع کلید', 'value' => ''], ['label' => 'جنس بدنه', 'value' => ''],
                            ['label' => 'قدرت مکش (مترمکعب بر ساعت)', 'value' => ''], ['label' => 'نوع فیلتر', 'value' => ''],
                            ['label' => 'تعداد موتور', 'value' => ''], ['label' => 'میزان صدا (دسی‌بل)', 'value' => ''],
                            ['label' => 'نوع خروجی هوا', 'value' => ''], ['label' => 'لوازم همراه', 'value' => ''],
                        ],
                        'gas' => [
                            ['label' => 'ابعاد کلی', 'value' => ''], ['label' => 'طول', 'value' => ''],
                            ['label' => 'عرض', 'value' => ''], ['label' => 'ارتفاع', 'value' => ''],
                            ['label' => 'تعداد شعله', 'value' => ''], ['label' => 'محدوده سایز', 'value' => ''],
                            ['label' => 'محل شعله پلوپز', 'value' => ''], ['label' => 'جنس صفحه', 'value' => ''],
                            ['label' => 'نوع شیشه', 'value' => ''], ['label' => 'ضخامت شیشه', 'value' => ''],
                            ['label' => 'جنس شبکه', 'value' => ''], ['label' => 'نوع ولوم', 'value' => ''],
                            ['label' => 'جنس ولوم', 'value' => ''], ['label' => 'نوع فندک', 'value' => ''],
                            ['label' => 'نوع سرشعله', 'value' => ''], ['label' => 'نوع شیر', 'value' => ''],
                            ['label' => 'سیستم ایمنی و ترموکوپل', 'value' => ''], ['label' => 'رده مصرف انرژی', 'value' => ''],
                            ['label' => 'منبع انرژی', 'value' => ''], ['label' => 'کابل برق', 'value' => ''],
                            ['label' => 'لوازم همراه', 'value' => ''], ['label' => 'مدل کالا / کد محصول', 'value' => ''],
                        ],
                    ];
                    $savedSpecifications = old('specifications', $product->specifications ?? []);
                    $initialSpecifications = [];
                    if (is_array($savedSpecifications) && array_is_list($savedSpecifications)) {
                        $initialSpecifications = collect($savedSpecifications)
                            ->filter(fn ($row) => is_array($row) && (filled($row['label'] ?? null) || filled($row['value'] ?? null)))
                            ->map(fn ($row) => ['label' => (string) ($row['label'] ?? ''), 'value' => (string) ($row['value'] ?? '')])
                            ->values()->all();
                    } elseif (is_array($savedSpecifications)) {
                        foreach ($savedSpecifications as $key => $value) {
                            if (is_scalar($value) && filled(trim((string) $value))) {
                                $initialSpecifications[] = [
                                    'label' => $specificationLabels[$key] ?? str_replace('_', ' ', (string) $key),
                                    'value' => (string) $value,
                                ];
                            }
                        }
                    }
                    $selectedCategory = $categories->firstWhere('id', old('category_id', $product->category_id ?? null));
                    $categoryIdentifier = mb_strtolower(($selectedCategory?->slug ?? '').' '.($selectedCategory?->name ?? ''));
                    $initialSpecificationType = str_contains($categoryIdentifier, 'sink') || str_contains($categoryIdentifier, 'سینک')
                        ? 'sink'
                        : ((str_contains($categoryIdentifier, 'hood') || str_contains($categoryIdentifier, 'هود'))
                            ? 'hood'
                            : ((str_contains($categoryIdentifier, 'gas') || str_contains($categoryIdentifier, 'اجاق') || str_contains($categoryIdentifier, 'گاز'))
                                ? 'gas' : ''));
                    if ($initialSpecifications === [] && $initialSpecificationType !== '') {
                        $initialSpecifications = $specificationTemplates[$initialSpecificationType];
                    }
                @endphp
                <section id="product-specifications" class="sm:col-span-2 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5"
                    data-specification-templates='@json($specificationTemplates)'
                    data-specification-initial='@json($initialSpecifications)'>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-[var(--admin-text)]">مشخصات فنی محصول</h3>
                            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">با انتخاب دسته‌بندی، فهرست پیشنهادی همان کالا نمایش داده می‌شود. هر ردیف قابل ویرایش، حذف و جابه‌جایی است؛ مقدارها اختیاری و متنی هستند.</p>
                        </div>
                        <button type="button" id="add-product-specification" class="admin-btn admin-btn-secondary">افزودن مشخصه</button>
                    </div>
                    <div id="product-specification-rows" class="mt-4 space-y-3" aria-live="polite"></div>
                    <p class="mt-3 text-[11px] text-[var(--admin-muted)]">نمونه مقدار: ۸۶×۵۰×۱۵ سانتی‌متر · فیلدهای خالی در صفحه محصول نمایش داده نمی‌شوند.</p>
                </section>

                {{-- Description --}}
                <div class="sm:col-span-2">

                    <label for="description" class="admin-label">
                        توضیحات کامل
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="9"
                        class="admin-textarea"
                    >{{ old('description', $product->description ?? '') }}</textarea>

                    @error('description')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

                @include('admin.products.partials.faq-fields')

            </div>

        </div>


        {{-- ========================================================
             Pricing
        ========================================================= --}}
        <div class="admin-card p-6">

            <div class="mb-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    قیمت و موجودی
                </h3>

                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">
                    قیمت فروش، قیمت قبل و موجودی محصول را مشخص کنید.
                    هنگام ورود مبلغ، اعداد به‌صورت سه‌رقمی نمایش داده می‌شوند.
                </p>

            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                {{-- =================================================
                     Price
                ================================================== --}}
                <div>

                    <label for="price" class="admin-label">
                        قیمت فروش
                    </label>

                    <div class="relative">

                        <input
                            id="price"
                            name="price"
                            type="text"
                            inputmode="decimal"
                            autocomplete="off"
                            value="{{ old('price', $product->price ?? '') }}"
                            class="admin-input pl-16"
                            placeholder="15,000,000"
                            required
                        >

                        <span
                            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]"
                        >
                            تومان
                        </span>

                    </div>

                    <p class="mt-2 text-xs leading-5 text-[var(--admin-muted)]">
                        قیمت فعلی فروش محصول.
                    </p>

                    @error('price')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- =================================================
                     Compare At Price
                ================================================== --}}
                <div>

                    <label for="compare_at_price" class="admin-label">
                        قیمت قبل
                    </label>

                    <div class="relative">

                        <input
                            id="compare_at_price"
                            name="compare_at_price"
                            type="text"
                            inputmode="decimal"
                            autocomplete="off"
                            value="{{ old('compare_at_price', $product->compare_at_price ?? '') }}"
                            class="admin-input pl-16"
                            placeholder="20,000,000"
                        >

                        <span
                            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]"
                        >
                            تومان
                        </span>

                    </div>

                    <p class="mt-2 text-xs leading-5 text-[var(--admin-muted)]">
                        قیمت محصول قبل از تخفیف؛ در صورت نداشتن تخفیف خالی بگذارید.
                    </p>

                    @error('compare_at_price')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- =================================================
                     Stock
                ================================================== --}}
                <div>

                    <label for="stock" class="admin-label">
                        موجودی
                    </label>

                    <div class="relative">

                        <input
                            id="stock"
                            name="stock"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            value="{{ old('stock', $product->stock ?? 0) }}"
                            class="admin-input pl-16"
                            placeholder="10"
                        >

                        <span
                            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]"
                        >
                            عدد
                        </span>

                    </div>

                    <p class="mt-2 text-xs leading-5 text-[var(--admin-muted)]">
                        تعداد موجودی قابل فروش محصول.
                    </p>

                    @error('stock')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

            </div>

        </div>


        {{-- ========================================================
             Installment
        ========================================================= --}}
        <div class="admin-card p-6">

            <div class="mb-6">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <h3 class="text-base font-bold text-[var(--admin-text)]">
                            فروش اقساطی
                        </h3>

                        <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">
                            شرایط فروش اقساطی این محصول را مشخص کنید.
                            محاسبه مبلغ نقدی و چک‌ها به‌صورت خودکار انجام می‌شود.
                        </p>

                    </div>

                    <span
                        id="installment_status_badge"
                        class="rounded-full border border-[var(--admin-border)] px-3 py-1 text-[11px] font-medium text-[var(--admin-muted)]"
                    >
                        {{ $installmentEnabled ? 'فعال' : 'غیرفعال' }}
                    </span>

                </div>

            </div>


            <div class="space-y-5">

                {{-- Enabled --}}
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">

                    <input
                        type="hidden"
                        name="installment_enabled"
                        value="0"
                    >

                    <input
                        id="installment_enabled"
                        type="checkbox"
                        name="installment_enabled"
                        value="1"
                        class="admin-checkbox mt-1 h-4 w-4"
                        @checked($installmentEnabled)
                    >

                    <span>

                        <span class="block text-sm font-semibold text-[var(--admin-text)]">
                            فعال‌سازی فروش اقساطی
                        </span>

                        <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">
                            مشتری می‌تواند این محصول را طبق شرایط تعریف‌شده به‌صورت اقساطی خریداری کند.
                        </span>

                    </span>

                </label>


                {{-- Installment Settings --}}
                <div
                    id="installment_settings"
                    class="{{ $installmentEnabled ? '' : 'hidden' }} space-y-5"
                >

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        {{-- Cash Percent --}}
                        <div>

                            <label
                                for="installment_cash_percent"
                                class="admin-label"
                            >
                                درصد پیش‌پرداخت
                            </label>

                            <div class="relative">

                                <input
                                    id="installment_cash_percent"
                                    name="installment_cash_percent"
                                    type="number"
                                    min="1"
                                    max="99"
                                    step="1"
                                    value="{{ $cashPercent }}"
                                    class="admin-input pr-12"
                                >

                                <span
                                    class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]"
                                >
                                    %
                                </span>

                            </div>

                            @error('installment_cash_percent')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>


                        {{-- Remainder Method --}}
                        <div>

                            <label
                                for="installment_remainder_method"
                                class="admin-label"
                            >
                                روش تسویه باقی‌مانده
                            </label>

                            <select
                                id="installment_remainder_method"
                                name="installment_remainder_method"
                                class="admin-select"
                            >
                                <option
                                    value="cheque"
                                    @selected($remainderMethod === 'cheque')
                                >
                                چک
                                </option>
                            </select>

                            @error('installment_remainder_method')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>


                        {{-- Cheque Count --}}
                        <div>

                            <label
                                for="installment_cheque_count"
                                class="admin-label"
                            >
                                تعداد چک
                            </label>

                            <input
                                id="installment_cheque_count"
                                name="installment_cheque_count"
                                type="number"
                                min="1"
                                max="30"
                                step="1"
                                value="{{ $chequeCount }}"
                                class="admin-input"
                            >

                            @error('installment_cheque_count')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>


                        {{-- Interval --}}
                        <div>

                            <label
                                for="installment_interval_months"
                                class="admin-label"
                            >
                                فاصله سررسید
                            </label>

                            <div class="relative">

                                <input
                                    id="installment_interval_months"
                                    name="installment_interval_months"
                                    type="number"
                                    min="1"
                                    max="24"
                                    step="1"
                                    value="{{ $intervalMonths }}"
                                    class="admin-input pl-16"
                                >

                                <span
                                    class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]"
                                >
                                    ماه
                                </span>

                            </div>

                            @error('installment_interval_months')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>

                    </div>


                    {{-- Live Preview --}}
                    <div
                        id="installment_preview"
                        class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5"
                    >

                        <div class="mb-4 flex items-center justify-between">

                            <div>

                                <p class="text-sm font-semibold text-[var(--admin-text)]">
                                    پیش‌نمایش شرایط اقساط
                                </p>

                                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                    مبالغ بر اساس قیمت محصول محاسبه می‌شوند.
                                </p>

                            </div>

                        </div>


                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                            {{-- Total --}}
                            <div
                                class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-4"
                            >

                                <p class="text-xs text-[var(--admin-muted)]">
                                    قیمت محصول
                                </p>

                                <p
                                    id="installment_total_preview"
                                    class="mt-2 text-sm font-bold text-[var(--admin-text)]"
                                >
                                    ۰ تومان
                                </p>

                            </div>


                            {{-- Cash --}}
                            <div
                                class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-4"
                            >

                                <p class="text-xs text-[var(--admin-muted)]">
                                    پیش‌پرداخت
                                </p>

                                <p
                                    id="installment_cash_preview"
                                    class="mt-2 text-sm font-bold text-[var(--admin-text)]"
                                >
                                    ۰ تومان
                                </p>

                            </div>


                            {{-- Deferred --}}
                            <div
                                class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-4"
                            >

                                <p class="text-xs text-[var(--admin-muted)]">
                                    باقی‌مانده
                                </p>

                                <p
                                    id="installment_deferred_preview"
                                    class="mt-2 text-sm font-bold text-[var(--admin-text)]"
                                >
                                    ۰ تومان
                                </p>

                            </div>

                        </div>


                        {{-- Cheques --}}
                        <div
                            id="installment_cheques_preview"
                            class="mt-4 space-y-3"
                        ></div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================
             SEO
        ========================================================= --}}
        <div class="admin-card p-6">

            <div class="mb-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    بهینه‌سازی موتور جستجو
                </h3>

                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">
                    عنوان و توضیحات صفحه محصول را برای نتایج جستجو تنظیم کنید.
                </p>

            </div>


            <div class="space-y-6">

                {{-- Meta Title --}}
                <div>

                    <div class="mb-2 flex items-center justify-between gap-3">

                        <label
                            for="meta_title"
                            class="admin-label mb-0"
                        >
                            Meta Title
                        </label>

                        <span
                            id="meta_title_counter"
                            class="text-[11px] text-[var(--admin-muted)]"
                        >
                            0 / 255
                        </span>

                    </div>


                    <input
                        id="meta_title"
                        name="meta_title"
                        type="text"
                        maxlength="255"
                        value="{{ old('meta_title', $product->meta_title ?? '') }}"
                        class="admin-input"
                        placeholder="مثلاً هود آشپزخانه مدل آریا | فرزین"
                    >


                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                        عنوانی که در تب مرورگر و نتایج جستجو نمایش داده می‌شود.
                        در صورت خالی بودن، عنوان محصول استفاده می‌شود.
                    </p>


                    @error('meta_title')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Meta Description --}}
                <div>

                    <div class="mb-2 flex items-center justify-between gap-3">

                        <label
                            for="meta_description"
                            class="admin-label mb-0"
                        >
                            Meta Description
                        </label>

                        <span
                            id="meta_description_counter"
                            class="text-[11px] text-[var(--admin-muted)]"
                        >
                            0 کاراکتر
                        </span>

                    </div>


                    <textarea
                        id="meta_description"
                        name="meta_description"
                        rows="5"
                        class="admin-textarea"
                        placeholder="توضیح کوتاه و جذاب درباره محصول برای موتورهای جستجو..."
                    >{{ old('meta_description', $product->meta_description ?? '') }}</textarea>


                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                        توضیح مختصر و واقعی درباره محصول، ویژگی‌ها و کاربرد آن بنویسید.
                    </p>


                    @error('meta_description')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium text-[var(--admin-text)]">Canonical URL
                        <input name="canonical_url" type="url" inputmode="url" dir="ltr" maxlength="2048" value="{{ old('canonical_url', $product->canonical_url ?? '') }}" placeholder="https://farajihome.com/product/..." class="admin-input mt-2">
                        @error('canonical_url')<span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="block text-sm font-medium text-[var(--admin-text)]">قانون ربات‌ها
                        <select name="seo_robots" class="admin-select mt-2 w-full">
                            @foreach (['inherit' => 'ارث‌بری از سایت', 'index,follow' => 'Index, Follow', 'noindex,follow' => 'Noindex, Follow', 'noindex,nofollow' => 'Noindex, Nofollow'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('seo_robots', $product->seo_robots ?: 'inherit') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('seo_robots')<span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label class="block text-sm font-medium text-[var(--admin-text)]">عنوان Open Graph
                    <input name="og_title" maxlength="255" value="{{ old('og_title', $product->og_title ?? '') }}" class="admin-input mt-2 w-full">
                    @error('og_title')<span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium text-[var(--admin-text)]">توضیحات Open Graph
                    <textarea name="og_description" rows="3" maxlength="320" class="admin-textarea mt-2 w-full">{{ old('og_description', $product->og_description ?? '') }}</textarea>
                    @error('og_description')<span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>@enderror
                </label>

                <p class="text-xs leading-6 text-[var(--admin-muted)]">قانون سراسری پیش‌انتشار همیشه اولویت دارد؛ تنظیم Index برای یک محصول نمی‌تواند قفل noindex سایت را دور بزند.</p>

                {{-- Google Preview --}}
                <div
                    class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5"
                >

                    <div class="mb-4">

                        <p class="text-sm font-semibold text-[var(--admin-text)]">
                            پیش‌نمایش نتیجه جستجو
                        </p>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            یک Preview تقریبی برای بررسی عنوان و توضیحات صفحه.
                        </p>

                    </div>


                    <div
                        class="rounded-xl bg-[var(--admin-surface)] p-4"
                    >

                        <p
                            id="seo_preview_title"
                            class="text-base font-medium text-[#1a0dab]"
                        >
                            {{ old('meta_title', $product->meta_title ?? '') ?: 'عنوان محصول شما' }}
                        </p>


                        <p
                            id="seo_preview_url"
                            class="mt-1 text-xs text-emerald-700"
                        >
                            {{ isset($product) && $product->slug
                                ? url('/product/' . $product->slug)
                                : url('/product/example') }}
                        </p>


                        <p
                            id="seo_preview_description"
                            class="mt-2 text-sm leading-7 text-[var(--admin-muted)]"
                        >
                            {{ old('meta_description', $product->meta_description ?? '') ?: 'توضیحات متا محصول شما اینجا نمایش داده می‌شود.' }}
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         Sidebar
    ============================================================ --}}
    <div class="space-y-6">

        {{-- ========================================================
             Publish
        ========================================================= --}}
        <div class="admin-card p-6">

            <div class="mb-5">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    انتشار
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    وضعیت نمایش محصول را تعیین کنید.
                </p>

            </div>


            <div>

                <label
                    for="status"
                    class="admin-label"
                >
                    وضعیت
                </label>


                <select
                    id="status"
                    name="status"
                    class="admin-select"
                    required
                >

                    <option
                        value="draft"
                        @selected(old('status', $product->status ?? 'active') === 'draft')
                    >
                    پیش‌نویس
                    </option>


                    <option
                        value="active"
                        @selected(old('status', $product->status ?? 'active') === 'active')
                    >
                    فعال
                    </option>


                    <option
                        value="archived"
                        @selected(old('status', $product->status ?? '') === 'archived')
                    >
                    آرشیو
                    </option>

                </select>


                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                    فقط محصولات «فعال» در فروشگاه، صفحه دسته‌بندی و صفحه محصول برای مشتری قابل مشاهده‌اند. پیش‌نویس و آرشیو عمومی نیستند.
                </p>

                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                    محصول جدید به‌صورت پیش‌فرض منتشر می‌شود. برای آماده‌سازی و بازبینی قبل از انتشار، «پیش‌نویس» را انتخاب کنید.
                </p>

                @error('status')
                <p class="mt-2 text-xs text-[var(--admin-danger)]">
                    {{ $message }}
                </p>
                @enderror

            </div>

        </div>


        {{-- ========================================================
             Flags
        ========================================================= --}}
        <div class="admin-card p-6">

            <div class="mb-5">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    ویژگی‌های محصول
                </h3>

            </div>


            <div class="space-y-3">

                {{-- Featured --}}
                <label
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4"
                >

                    <input
                        type="hidden"
                        name="is_featured"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="is_featured"
                        value="1"
                        class="admin-checkbox mt-1 h-4 w-4"
                        @checked(old('is_featured', $product->is_featured ?? false))
                    >


                    <span>

                        <span class="block text-sm font-semibold text-[var(--admin-text)]">
                            محصول ویژه
                        </span>

                        <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">
                            محصول در بخش محصولات ویژه قرار می‌گیرد.
                        </span>

                    </span>

                </label>


                {{-- New --}}
                <label
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4"
                >
                    <input type="hidden" name="is_new" value="0">
                    <input
                        type="checkbox"
                        name="is_new"
                        value="1"
                        class="admin-checkbox mt-1 h-4 w-4"
                        @checked(old('is_new', $product->is_new ?? false))
                    >
                    <span>
                        <span class="block text-sm font-semibold text-[var(--admin-text)]">محصول جدید</span>
                        <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">
                            برچسب «جدید» روی محصول نمایش داده می‌شود.
                        </span>
                    </span>
                </label>

                {{-- Homepage product selection --}}
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                    <input type="hidden" name="show_in_home_gallery" value="0">
                    <input
                        type="checkbox"
                        name="show_in_home_gallery"
                        value="1"
                        class="admin-checkbox mt-1 h-4 w-4"
                        @checked(old('show_in_home_gallery', $product->show_in_home_gallery ?? !$editing))
                    >
                    <span>
                        <span class="block text-sm font-semibold text-[var(--admin-text)]">نمایش در بخش محصولات منتخب صفحه اصلی</span>
                        <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">
                            این گزینه برای نمایش محصول در بخش محصولات منتخب صفحه اصلی است. اگر خاموش باشد، محصول فقط در فروشگاه و دسته‌بندی خودش نمایش داده می‌شود. برای انتشار عمومی، وضعیت محصول، تصویر اصلی و دسته‌بندی فعال هم لازم است.
                        </span>
                    </span>
                </label>

            </div>

        </div>


    </div>

</div>

<div class="fixed inset-x-0 bottom-0 z-40 border-t border-[var(--admin-border)] bg-[var(--admin-white)]/95 px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-[0_-8px_24px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="mx-auto flex max-w-7xl flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary w-full sm:w-auto">انصراف</a>
        <button type="submit" class="admin-btn admin-btn-primary w-full sm:min-w-52 sm:w-auto">
            {{ $editing ? 'ذخیره تغییرات' : 'ایجاد محصول' }}
        </button>
    </div>
</div>

{{-- ================================================================
     Scripts
================================================================ --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /*
            |--------------------------------------------------------------------------
            | Elements
            |--------------------------------------------------------------------------
            */

            const form =
                document.querySelector('[data-admin-product-form]');

            const priceInput =
                document.getElementById('price');

            const comparePriceInput =
                document.getElementById('compare_at_price');

            const stockInput =
                document.getElementById('stock');

            const imageInput =
                document.getElementById('image');

            const imagePreview =
                document.getElementById('product-image-preview');


            const enabledInput =
                document.getElementById('installment_enabled');

            const settings =
                document.getElementById('installment_settings');

            const statusBadge =
                document.getElementById('installment_status_badge');


            const cashPercentInput =
                document.getElementById('installment_cash_percent');

            const chequeCountInput =
                document.getElementById('installment_cheque_count');

            const intervalInput =
                document.getElementById('installment_interval_months');


            const totalPreview =
                document.getElementById('installment_total_preview');

            const cashPreview =
                document.getElementById('installment_cash_preview');

            const deferredPreview =
                document.getElementById('installment_deferred_preview');

            const chequesPreview =
                document.getElementById('installment_cheques_preview');


            const titleInput =
                document.getElementById('meta_title');

            const descriptionInput =
                document.getElementById('meta_description');

            const titleCounter =
                document.getElementById('meta_title_counter');

            const descriptionCounter =
                document.getElementById('meta_description_counter');

            const previewTitle =
                document.getElementById('seo_preview_title');

            const previewDescription =
                document.getElementById('seo_preview_description');

            const nameInput =
                document.getElementById('name');


            /*
            |--------------------------------------------------------------------------
            | Number Helpers
            |--------------------------------------------------------------------------
            */

            function normalizeDigits(value) {

                return String(value || '')
                    .replace(/[۰-۹]/g, function (digit) {

                        return '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit);

                    })
                    .replace(/[٠-٩]/g, function (digit) {

                        return '٠١٢٣٤٥٦٧٨٩'.indexOf(digit);

                    });

            }


            function getRawNumber(value, allowDecimal = false) {

                let raw =
                    normalizeDigits(value)
                        .replace(/,/g, '')
                        .replace(/٬/g, '')
                        .replace(/\s+/g, '')
                        .replace(/٫/g, '.');

                if (!allowDecimal) {
                    return raw.replace(/[^\d]/g, '');
                }

                raw = raw.replace(/[^\d.]/g, '');

                const firstDot =
                    raw.indexOf('.');

                if (firstDot !== -1) {
                    raw =
                        raw.slice(0, firstDot + 1) +
                        raw.slice(firstDot + 1).replace(/\./g, '');
                }

                return raw;
            }


            function formatNumber(value, allowDecimal = false) {

                const raw =
                    getRawNumber(
                        value,
                        allowDecimal
                    );

                if (!raw) {
                    return '';
                }

                if (!allowDecimal || !raw.includes('.')) {
                    return Number(raw).toLocaleString('fa-IR');
                }

                const parts =
                    raw.split('.');

                const integerPart =
                    parts[0] || '0';

                const decimalPart =
                    parts[1] || '';

                return (
                    Number(integerPart).toLocaleString('fa-IR') +
                    '.' +
                    decimalPart
                );
            }


            function formatMoney(value) {

                return `${Number(value || 0).toLocaleString('fa-IR')} تومان`;

            }


            /*
            |--------------------------------------------------------------------------
            | Price / Number Input Formatting
            |--------------------------------------------------------------------------
            */

            function setupNumberInput(input, allowDecimal = false) {

                if (!input) {
                    return;
                }


                /*
                 * Format existing value when editing
                 *
                 * 15000000
                 * ↓
                 * 15,000,000
                 */
                input.value =
                    formatNumber(
                        input.value,
                        allowDecimal
                    );


                input.addEventListener('input', function () {

                    const oldValue =
                        input.value;

                    const cursorPosition =
                        input.selectionStart || 0;


                    /*
                     * How many actual digits
                     * were before cursor?
                     */
                    const digitsBeforeCursor =
                        getRawNumber(
                            oldValue.substring(
                                0,
                                cursorPosition
                            ),
                            allowDecimal
                        ).length;


                    /*
                     * Format number
                     */
                    input.value =
                        formatNumber(
                            oldValue,
                            allowDecimal
                        );


                    /*
                     * Restore cursor position
                     */
                    let newCursorPosition = 0;

                    let digitCount = 0;


                    for (
                        let i = 0;
                        i < input.value.length;
                        i++
                    ) {

                        if (/\d/.test(input.value[i])) {

                            digitCount++;

                        }


                        newCursorPosition =
                            i + 1;


                        if (
                            digitCount >=
                            digitsBeforeCursor
                        ) {

                            break;

                        }

                    }


                    input.setSelectionRange(
                        newCursorPosition,
                        newCursorPosition
                    );


                    /*
                     * Price changes should update
                     * installment preview.
                     */
                    if (
                        input === priceInput &&
                        enabledInput &&
                        enabledInput.checked
                    ) {

                        updateInstallmentPreview();

                    }

                });

            }


            setupNumberInput(
                priceInput,
                true
            );

            setupNumberInput(
                comparePriceInput,
                true
            );

            setupNumberInput(
                stockInput,
                false
            );


            /*
            |--------------------------------------------------------------------------
            | Instant Image Preview
            |--------------------------------------------------------------------------
            */

            const imagePlaceholder =
                document.getElementById('product-image-placeholder');

            if (imageInput && imagePreview) {

                imageInput.addEventListener('change', function () {

                    const file =
                        imageInput.files?.[0];

                    if (!file || !file.type.startsWith('image/')) {
                        return;
                    }

                    const objectUrl =
                        URL.createObjectURL(file);

                    imagePreview.src =
                        objectUrl;

                    imagePreview.classList.remove('hidden');

                    if (imagePlaceholder) {
                        imagePlaceholder.classList.add('hidden');
                        imagePlaceholder.classList.remove('flex');
                    }

                    imagePreview.onload =
                        function () {
                            URL.revokeObjectURL(objectUrl);
                        };

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Installment Status
            |--------------------------------------------------------------------------
            */

            function updateInstallmentStatus() {

                if (!enabledInput) {
                    return;
                }


                const enabled =
                    enabledInput.checked;


                if (settings) {

                    settings.classList.toggle(
                        'hidden',
                        !enabled
                    );

                }


                if (statusBadge) {

                    statusBadge.textContent =
                        enabled
                            ? 'فعال'
                            : 'غیرفعال';

                }


                if (enabled) {

                    updateInstallmentPreview();

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Installment Preview
            |--------------------------------------------------------------------------
            */

            function updateInstallmentPreview() {

                if (
                    !enabledInput ||
                    !enabledInput.checked
                ) {

                    return;

                }


                /*
                 * IMPORTANT:
                 *
                 * Price is visually:
                 *
                 * 15,000,000
                 *
                 * But calculation must use:
                 *
                 * 15000000
                 */
                const total =
                    Number(
                        getRawNumber(
                            priceInput?.value || '',
                            true
                        ) || 0
                    );


                const cashPercent =
                    Number(
                        cashPercentInput?.value || 0
                    );


                const chequeCount =
                    Math.max(
                        1,
                        Number(
                            chequeCountInput?.value || 1
                        )
                    );


                const intervalMonths =
                    Math.max(
                        1,
                        Number(
                            intervalInput?.value || 1
                        )
                    );


                /*
                 * Invalid values
                 */
                if (
                    total <= 0 ||
                    cashPercent <= 0
                ) {

                    if (totalPreview) {

                        totalPreview.textContent =
                            formatMoney(0);

                    }


                    if (cashPreview) {

                        cashPreview.textContent =
                            formatMoney(0);

                    }


                    if (deferredPreview) {

                        deferredPreview.textContent =
                            formatMoney(0);

                    }


                    if (chequesPreview) {

                        chequesPreview.innerHTML =
                            '';

                    }


                    return;

                }


                /*
                 * Cash payment
                 */
                const cashAmount =
                    Math.round(
                        total *
                        (cashPercent / 100)
                    );


                /*
                 * Remaining amount
                 */
                const deferredAmount =
                    Math.max(
                        0,
                        total - cashAmount
                    );


                /*
                 * Main cards
                 */
                if (totalPreview) {

                    totalPreview.textContent =
                        formatMoney(total);

                }


                if (cashPreview) {

                    cashPreview.textContent =
                        formatMoney(cashAmount);

                }


                if (deferredPreview) {

                    deferredPreview.textContent =
                        formatMoney(deferredAmount);

                }


                /*
                 * Cheques
                 */
                if (!chequesPreview) {
                    return;
                }


                const baseAmount =
                    Math.floor(
                        deferredAmount /
                        chequeCount
                    );


                let distributed = 0;

                const rows = [];


                for (
                    let index = 1;
                    index <= chequeCount;
                    index++
                ) {

                    let amount;


                    /*
                     * Last cheque receives
                     * the rounding remainder.
                     */
                    if (
                        index === chequeCount
                    ) {

                        amount =
                            deferredAmount -
                            distributed;

                    } else {

                        amount =
                            baseAmount;

                    }


                    distributed += amount;


                    const months =
                        intervalMonths *
                        index;


                    rows.push(`

                <div
                    class="flex items-center justify-between rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-4 py-3"
                >

                    <div>

                        <p class="text-xs text-[var(--admin-muted)]">
                            چک ${Number(index).toLocaleString('fa-IR')}
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[var(--admin-text)]">
                            ${formatMoney(amount)}
                        </p>

                    </div>


                    <span
                        class="rounded-full border border-[var(--admin-border)] px-3 py-1 text-xs text-[var(--admin-muted)]"
                    >
                        ${Number(months).toLocaleString('fa-IR')} ماه بعد
                    </span>

                </div>

            `);

                }


                chequesPreview.innerHTML =
                    rows.join('');

            }


            /*
            |--------------------------------------------------------------------------
            | SEO Preview
            |--------------------------------------------------------------------------
            */

            function updateSeoPreview() {

                if (
                    !titleInput ||
                    !descriptionInput
                ) {

                    return;

                }


                const title =
                    titleInput.value.trim();


                const description =
                    descriptionInput.value.trim();


                if (titleCounter) {

                    titleCounter.textContent =
                        `${title.length} / 255`;

                }


                if (descriptionCounter) {

                    descriptionCounter.textContent =
                        `${description.length} کاراکتر`;

                }


                if (previewTitle) {

                    previewTitle.textContent =
                        title ||
                        nameInput?.value.trim() ||
                        'عنوان محصول شما';

                }


                if (previewDescription) {

                    previewDescription.textContent =
                        description ||
                        'توضیحات متا محصول شما اینجا نمایش داده می‌شود.';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Events
            |--------------------------------------------------------------------------
            */

            if (enabledInput) {

                enabledInput.addEventListener(
                    'change',
                    updateInstallmentStatus
                );

            }


            [
                cashPercentInput,
                chequeCountInput,
                intervalInput
            ]
                .filter(Boolean)
                .forEach(function (input) {

                    input.addEventListener(
                        'input',
                        updateInstallmentPreview
                    );

                });


            if (titleInput) {

                titleInput.addEventListener(
                    'input',
                    updateSeoPreview
                );

            }


            if (descriptionInput) {

                descriptionInput.addEventListener(
                    'input',
                    updateSeoPreview
                );

            }


            if (nameInput) {

                nameInput.addEventListener(
                    'input',
                    updateSeoPreview
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Form Submit
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | User sees:
            |
            | 15,000,000
            |
            | Backend receives:
            |
            | 15000000
            |
            */

            if (form) {

                form.addEventListener(
                    'submit',
                    function () {

                        if (priceInput) {

                            priceInput.value =
                                getRawNumber(
                                    priceInput.value,
                                    true
                                );

                        }


                        if (comparePriceInput) {

                            comparePriceInput.value =
                                getRawNumber(
                                    comparePriceInput.value,
                                    true
                                );

                        }


                        if (stockInput) {

                            stockInput.value =
                                getRawNumber(
                                    stockInput.value
                                );

                        }

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Initial State
            |--------------------------------------------------------------------------
            */

            updateInstallmentStatus();

            updateSeoPreview();

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.getElementById('product-specifications');
            const category = document.getElementById('category_id');
            const rowsElement = document.getElementById('product-specification-rows');
            const addButton = document.getElementById('add-product-specification');
            if (!root || !category || !rowsElement || !addButton) return;

            const templates = JSON.parse(root.dataset.specificationTemplates || '{}');
            let rows = JSON.parse(root.dataset.specificationInitial || '[]');
            const detectType = () => {
                const selected = category.options[category.selectedIndex];
                const value = ((selected?.textContent || '') + ' ' + (selected?.dataset.slug || '')).toLocaleLowerCase('fa');
                if (value.includes('سینک') || value.includes('sink')) return 'sink';
                if (value.includes('هود') || value.includes('hood')) return 'hood';
                if (value.includes('اجاق') || value.includes('گاز') || value.includes('gas')) return 'gas';
                return '';
            };
            const reindex = () => {
                [...rowsElement.querySelectorAll('[data-specification-row]')].forEach((row, index) => {
                    row.querySelector('[data-specification-label]').name = `specifications[${index}][label]`;
                    row.querySelector('[data-specification-value]').name = `specifications[${index}][value]`;
                });
            };
            const render = () => {
                rowsElement.replaceChildren();
                rows.forEach((item, index) => {
                    const row = document.createElement('div');
                    row.dataset.specificationRow = '';
                    row.className = 'grid grid-cols-1 gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-card)] p-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)_auto] sm:items-end';
                    const labelWrap = document.createElement('label');
                    labelWrap.className = 'block text-xs font-semibold text-[var(--admin-muted)]';
                    labelWrap.textContent = 'عنوان مشخصه';
                    const labelInput = document.createElement('input');
                    labelInput.type = 'text';
                    labelInput.maxLength = 100;
                    labelInput.className = 'admin-input mt-2 w-full';
                    labelInput.placeholder = 'مثلاً تعداد شعله';
                    labelInput.value = item.label || '';
                    labelInput.dataset.specificationLabel = '';
                    labelWrap.append(labelInput);

                    const valueWrap = document.createElement('label');
                    valueWrap.className = 'block text-xs font-semibold text-[var(--admin-muted)]';
                    valueWrap.textContent = 'مقدار';
                    const valueInput = document.createElement('input');
                    valueInput.type = 'text';
                    valueInput.maxLength = 1000;
                    valueInput.className = 'admin-input mt-2 w-full';
                    valueInput.placeholder = 'مقدار یا توضیح دلخواه؛ مانند ۸۶×۵۰×۱۵ سانتی‌متر';
                    valueInput.value = item.value || '';
                    valueInput.dataset.specificationValue = '';
                    valueWrap.append(valueInput);

                    const actions = document.createElement('div');
                    actions.className = 'flex items-center gap-1 sm:pb-0.5';
                    [['up', 'بالا'], ['down', 'پایین'], ['remove', 'حذف']].forEach(([action, title]) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.dataset.specificationAction = action;
                        button.textContent = title;
                        button.className = 'admin-btn admin-btn-secondary px-2 py-2 text-xs';
                        button.setAttribute('aria-label', title + ' کردن مشخصه ' + (index + 1));
                        if ((action === 'up' && index === 0) || (action === 'down' && index === rows.length - 1)) button.disabled = true;
                        actions.append(button);
                    });
                    row.append(labelWrap, valueWrap, actions);
                    rowsElement.append(row);
                });
                reindex();
            };
            const readRows = () => {
                rows = [...rowsElement.querySelectorAll('[data-specification-row]')].map(row => ({
                    label: row.querySelector('[data-specification-label]').value,
                    value: row.querySelector('[data-specification-value]').value
                }));
            };
            const addRow = (item = {label: '', value: ''}) => {
                readRows();
                rows.push(item);
                render();
                rowsElement.lastElementChild?.querySelector('[data-specification-label]')?.focus();
            };

            if (!Array.isArray(rows) || rows.length === 0) {
                const type = detectType();
                rows = type && templates[type] ? templates[type] : [];
            }
            render();

            category.addEventListener('change', () => {
                readRows();
                const type = detectType();
                rows = type && templates[type] ? templates[type].map(item => ({...item})) : [];
                render();
            });
            addButton.addEventListener('click', () => addRow());
            rowsElement.addEventListener('input', readRows);
            rowsElement.addEventListener('click', event => {
                const button = event.target.closest('[data-specification-action]');
                if (!button) return;
                readRows();
                const row = button.closest('[data-specification-row]');
                const index = [...rowsElement.children].indexOf(row);
                const action = button.dataset.specificationAction;
                if (action === 'remove') rows.splice(index, 1);
                if (action === 'up' && index > 0) [rows[index - 1], rows[index]] = [rows[index], rows[index - 1]];
                if (action === 'down' && index < rows.length - 1) [rows[index + 1], rows[index]] = [rows[index], rows[index + 1]];
                render();
            });
            const form = root.closest('form');
            form?.addEventListener('submit', () => {
                readRows();
                rows = rows.filter(item => (item.label || '').trim() !== '' || (item.value || '').trim() !== '');
                rowsElement.querySelectorAll('[data-specification-row]').forEach((row, index) => {
                    row.querySelector('[data-specification-label]').name = `specifications[${index}][label]`;
                    row.querySelector('[data-specification-value]').name = `specifications[${index}][value]`;
                });
            });
        });
    </script>
@endpush
