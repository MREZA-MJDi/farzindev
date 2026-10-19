@php
    $editing = isset($productVariant);

    $selectedImageIds = collect(
        old(
            'image_ids',
            isset($productVariant)
                ? $productVariant->images?->pluck('id')->all()
                : []
        )
    )
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

@csrf

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

    <div class="space-y-6 xl:col-span-2">

        {{-- Product --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    محصول
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    محصول مرتبط با این تنوع را انتخاب کنید.
                </p>
            </div>

            <label for="product_id" class="admin-label">
                محصول
            </label>

            <select
                id="product_id"
                name="product_id"
                class="admin-select"
                required
            >
                <option value="">
                    انتخاب محصول
                </option>

                @foreach($products as $product)

                    <option
                        value="{{ $product->id }}"
                        @selected(
                        old(
                    'product_id',
                    $productVariant->product_id ?? $selectedProductId ?? ''
                    ) == $product->id
                    )
                    >
                    {{ $product->name }}
                    </option>

                @endforeach

            </select>

            @error('product_id')
            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                {{ $message }}
            </p>
            @enderror

        </div>


        {{-- Variant Info --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    مشخصات تنوع
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    نوع، نام و مقدار ویژگی را مشخص کنید.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                <div>

                    <label for="type" class="admin-label">
                        نوع ویژگی
                    </label>

                    <input
                        id="type"
                        name="type"
                        type="text"
                        maxlength="255"
                        value="{{ old('type', $productVariant->type ?? '') }}"
                        class="admin-input"
                        placeholder="مثلاً رنگ"
                        required
                    >

                    @error('type')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div>

                    <label for="name" class="admin-label">
                        نام ویژگی
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        maxlength="255"
                        value="{{ old('name', $productVariant->name ?? '') }}"
                        class="admin-input"
                        placeholder="مثلاً رنگ بدنه"
                        required
                    >

                    @error('name')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div class="sm:col-span-2">

                    <label for="value" class="admin-label">
                        مقدار
                    </label>

                    <input
                        id="value"
                        name="value"
                        type="text"
                        maxlength="255"
                        value="{{ old('value', $productVariant->value ?? '') }}"
                        class="admin-input"
                        placeholder="مثلاً کرم"
                        required
                    >

                    @error('value')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>



                <div class="sm:col-span-2">

                    <label for="color_hex" class="admin-label">
                        کد رنگ
                    </label>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                        <input
                            id="color_hex"
                            name="color_hex"
                            type="text"
                            maxlength="20"
                            value="{{ old('color_hex', $productVariant->color_hex ?? '') }}"
                            dir="ltr"
                            class="admin-input flex-1 text-left"
                            placeholder="#355F65"
                        >

                        <p class="text-xs leading-6 text-[var(--admin-muted)]">
                            برای نوع «رنگ» اختیاری است؛ مثال:
                            <span dir="ltr">#355F65</span>
                        </p>

                    </div>

                    @error('color_hex')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div class="sm:col-span-2">

                    <label for="sku" class="admin-label">
                        SKU
                    </label>

                    <input
                        id="sku"
                        name="sku"
                        type="text"
                        maxlength="255"
                        value="{{ old('sku', $productVariant->sku ?? '') }}"
                        dir="ltr"
                        class="admin-input text-left"
                        placeholder="اختیاری"
                    >

                    @error('sku')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

            </div>

        </div>



        {{-- Variant Images --}}
        <div class="admin-card p-6">

            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">

                <div>
                    <h3 class="text-base font-bold text-[var(--admin-text)]">
                        تصاویر مرتبط با این تنوع
                    </h3>

                    <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">
                        برای رنگ یا نمای خاص، تصاویر محصول را به این تنوع وصل کنید.
                        با انتخاب این تنوع در صفحه محصول، این تصاویر برجسته می‌شوند.
                    </p>
                </div>

                @if($selectedProductId ?? null)
                    <a
                        href="{{ route('admin.product-images.create', ['product_id' => $selectedProductId]) }}"
                        class="admin-btn admin-btn-secondary"
                    >
                        افزودن تصویر
                    </a>
                @endif

            </div>


            @if(($selectedProductImages ?? collect())->isNotEmpty())

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">

                    @foreach($selectedProductImages as $image)

                        <label class="group relative cursor-pointer overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)]">

                            <input
                                type="checkbox"
                                name="image_ids[]"
                                value="{{ $image->id }}"
                                class="peer sr-only"
                                @checked(in_array($image->id, $selectedImageIds, true))
                            >

                            <img
                                src="{{ $image->url }}"
                                alt="{{ $image->alt ?? 'تصویر محصول' }}"
                                class="aspect-[4/3] w-full object-cover transition duration-200 group-hover:scale-[1.02]"
                            >

                            <span class="absolute inset-0 hidden bg-black/10 peer-checked:block"></span>

                            <span class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full border border-white/60 bg-white/90 text-xs opacity-0 shadow-sm peer-checked:opacity-100">
                                ✓
                            </span>

                            <span class="block border-t border-[var(--admin-border)] px-3 py-2 text-[10px] text-[var(--admin-muted)]">
                                #{{ $image->sort_order + 1 }}
                            </span>

                        </label>

                    @endforeach

                </div>

            @else

                <div class="rounded-2xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-6 text-center">

                    <p class="text-sm font-semibold text-[var(--admin-text)]">
                        هنوز تصویری برای این محصول ثبت نشده است.
                    </p>

                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                        ابتدا تصویر محصول را اضافه کنید تا بتوانید آن را به این تنوع متصل کنید.
                    </p>

                    @if($selectedProductId ?? null)
                        <a
                            href="{{ route('admin.product-images.create', ['product_id' => $selectedProductId]) }}"
                            class="admin-btn admin-btn-primary mt-4"
                        >
                            افزودن تصویر محصول
                        </a>
                    @endif

                </div>

            @endif


            @error('image_ids')
            <p class="mt-3 text-xs text-[var(--admin-danger)]">
                {{ $message }}
            </p>
            @enderror

            @error('image_ids.*')
            <p class="mt-3 text-xs text-[var(--admin-danger)]">
                {{ $message }}
            </p>
            @enderror

        </div>


        {{-- Pricing & Stock --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    قیمت و موجودی
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    میزان تغییر قیمت و موجودی این تنوع را تعیین کنید.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                <div>

                    <label for="price_adjustment" class="admin-label">
                        تعدیل قیمت
                    </label>

                    <div class="relative">

                        <input
                            id="price_adjustment"
                            name="price_adjustment"
                            type="number"
                            step="0.01"
                            value="{{ old('price_adjustment', $productVariant->price_adjustment ?? 0) }}"
                            class="admin-input pl-16"
                        >

                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]">
                            تومان
                        </span>

                    </div>

                    <p class="mt-2 text-xs text-[var(--admin-muted)]">
                        مقدار مثبت به قیمت محصول اضافه می‌کند و مقدار منفی کم می‌کند.
                    </p>

                    @error('price_adjustment')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div>

                    <label for="stock" class="admin-label">
                        موجودی
                    </label>

                    <input
                        id="stock"
                        name="stock"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('stock', $productVariant->stock ?? 0) }}"
                        class="admin-input"
                    >

                    @error('stock')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

            </div>

        </div>

    </div>


    {{-- Sidebar --}}
    <div class="space-y-6">

        {{-- Status --}}
        <div class="admin-card p-6">

            <div class="mb-5">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    وضعیت
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    فعال یا غیرفعال بودن این تنوع را تعیین کنید.
                </p>
            </div>


            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">

                <input
                    type="hidden"
                    name="is_active"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="admin-checkbox mt-1 h-4 w-4"
                    @checked(old('is_active', $productVariant->is_active ?? false))
                >

                <span>

                    <span class="block text-sm font-semibold text-[var(--admin-text)]">
                        تنوع فعال باشد
                    </span>

                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">
                        این تنوع در فروشگاه قابل استفاده خواهد بود.
                    </span>

                </span>

            </label>

            @error('is_active')
            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                {{ $message }}
            </p>
            @enderror

        </div>


        {{-- Summary --}}
        <div class="admin-card p-6">

            <h3 class="text-base font-bold text-[var(--admin-text)]">
                خلاصه
            </h3>

            <div class="mt-5 space-y-4">

                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs text-[var(--admin-muted)]">
                        حالت
                    </span>

                    <span class="admin-badge admin-badge-info">
                        {{ $editing ? 'ویرایش' : 'جدید' }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs text-[var(--admin-muted)]">
                        موجودی پیش‌فرض
                    </span>

                    <span class="text-xs font-semibold text-[var(--admin-text-soft)]">
                        0
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs text-[var(--admin-muted)]">
                        تعدیل قیمت پیش‌فرض
                    </span>

                    <span class="text-xs font-semibold text-[var(--admin-text-soft)]">
                        0 تومان
                    </span>
                </div>

            </div>

        </div>


        {{-- Save --}}
        <div class="admin-card p-6">

            <div class="flex flex-col gap-3">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary w-full"
                >
                    {{ $editing ? 'ذخیره تغییرات' : 'ایجاد تنوع' }}
                </button>

                <a
                    href="{{ route('admin.product-variants.index') }}"
                    class="admin-btn admin-btn-secondary w-full"
                >
                    انصراف
                </a>

            </div>

        </div>

    </div>

</div>
