@php
    $productImages = $product->images ?? collect();
    $allVariants = $product->allVariants ?? collect();
@endphp

<div class="space-y-6">
    <section class="admin-card p-5 sm:p-7">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
            <div>
                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">PRODUCT OPTIONS</p>
                <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">رنگ‌ها و تنوع‌های محصول</h2>
                <p class="mt-2 max-w-3xl text-xs leading-7 text-[var(--admin-muted)]">
                    هر تنوع متعلق به همین محصول است. مشخصات، موجودی، قیمت اضافه و تصاویر همان رنگ را همین‌جا تنظیم کنید؛ تصاویر را لازم نیست از صفحه‌ای جداگانه مدیریت کنید.
                </p>
            </div>
            <span class="admin-badge admin-badge-neutral">{{ $allVariants->count() }} تنوع</span>
        </div>

        @if($errors->any())
            <div role="alert" class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-xs leading-7 text-red-800">
                <p class="font-semibold">برخی موارد نیاز به اصلاح دارند.</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @if($productImages->isNotEmpty())
            <div class="mt-6">
                <p class="mb-3 text-xs font-semibold text-[var(--admin-text)]">تصاویر فعلی محصول</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($productImages as $image)
                        <div class="w-24">
                            <div class="aspect-square overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)]">
                                @if($image->url)<img src="{{ $image->url }}" alt="{{ $image->alt ?: $product->name }}" class="h-full w-full object-cover">@endif
                            </div>
                            <p class="mt-1 truncate text-[10px] text-[var(--admin-muted)]">{{ $image->is_primary ? 'تصویر اصلی' : ($image->alt ?: 'تصویر محصول') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="admin-card p-5 sm:p-7">
        <div class="mb-5">
            <h3 class="text-base font-bold text-[var(--admin-text)]">افزودن رنگ یا تنوع</h3>
            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">مثلاً نوع «color»، نام «رنگ»، مقدار «مشکی» و کد رنگ اختیاری #202020.</p>
        </div>

        <form method="POST" action="{{ route('admin.product-variants.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="return_to_product" value="1">

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <label class="block text-xs text-[var(--admin-text)]">نوع / گروه
                    <input name="type" value="{{ old('type', 'color') }}" list="variant-types" required maxlength="255" class="admin-input mt-2 w-full" dir="ltr" placeholder="color">
                </label>
                <label class="block text-xs text-[var(--admin-text)]">نام ویژگی
                    <input name="name" value="{{ old('name', 'رنگ') }}" required maxlength="255" class="admin-input mt-2 w-full">
                </label>
                <label class="block text-xs text-[var(--admin-text)]">مقدار نمایش‌داده‌شده
                    <input name="value" value="{{ old('value') }}" required maxlength="255" class="admin-input mt-2 w-full" placeholder="مشکی">
                </label>
                <label class="block text-xs text-[var(--admin-text)]">کد رنگ (اختیاری)
                    <div class="mt-2 flex gap-2">
                        <input id="new-variant-color-hex" name="color_hex" value="{{ old('color_hex') }}" maxlength="20" class="admin-input w-full" dir="ltr" placeholder="#202020">
                        <input type="color" aria-label="انتخاب رنگ" value="{{ old('color_hex', '#202020') }}" oninput="document.getElementById('new-variant-color-hex').value = this.value" class="h-11 w-12 shrink-0 rounded-lg border border-[var(--admin-border)] bg-white p-1">
                    </div>
                </label>
            </div>

            <datalist id="variant-types">
                <option value="color"></option><option value="size"></option><option value="finish"></option><option value="material"></option>
            </datalist>

            <div class="grid gap-4 sm:grid-cols-3">
                <label class="block text-xs text-[var(--admin-text)]">SKU تنوع (اختیاری)
                    <input name="sku" value="{{ old('sku') }}" maxlength="255" class="admin-input mt-2 w-full" dir="ltr">
                </label>
                <label class="block text-xs text-[var(--admin-text)]">تغییر قیمت (تومان)
                    <input name="price_adjustment" type="number" value="{{ old('price_adjustment', 0) }}" step="0.01" class="admin-input mt-2 w-full" dir="ltr">
                </label>
                <label class="block text-xs text-[var(--admin-text)]">موجودی این رنگ
                    <input name="stock" type="number" min="0" value="{{ old('stock', 0) }}" required class="admin-input mt-2 w-full" dir="ltr">
                </label>
            </div>

            @if($productImages->isNotEmpty())
                <div>
                    <p class="mb-3 text-xs font-semibold text-[var(--admin-text)]">اتصال تصاویر ثبت‌شده به این رنگ</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($productImages as $image)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[var(--admin-border)] p-3 text-xs">
                                <input type="checkbox" name="image_ids[]" value="{{ $image->id }}" class="admin-checkbox h-4 w-4">
                                <span class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-[var(--admin-surface-soft)]">@if($image->url)<img src="{{ $image->url }}" alt="" class="h-full w-full object-cover">@endif</span>
                                <span class="min-w-0"><span class="block truncate font-medium">{{ $image->alt ?: $product->name }}</span><span class="mt-1 block text-[10px] text-[var(--admin-muted)]">{{ $image->is_primary ? 'تصویر اصلی' : 'تصویر موجود' }}</span></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-xl border border-dashed border-[var(--admin-border)] p-4">
                <label class="block text-xs font-semibold text-[var(--admin-text)]">آپلود عکس‌های اختصاصی این رنگ (چند فایل)
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="admin-input mt-2 w-full p-2">
                </label>
                <p class="mt-2 text-[11px] leading-6 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ هر تصویر حداقل ۸۰۰×۶۰۰ و حداکثر ۲۰ مگابایت. فایل‌ها به همین محصول تعلق دارند و به تنوع متصل می‌شوند.</p>
                @error('images')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                @error('images.*')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="inline-flex items-center gap-2 text-xs text-[var(--admin-text)]">
                <input type="checkbox" name="is_active" value="1" checked class="admin-checkbox h-4 w-4">
                این تنوع فعال و قابل انتخاب باشد
            </label>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="admin-btn admin-btn-primary">افزودن تنوع و تصاویر</button>
                <p class="self-center text-[11px] text-[var(--admin-muted)]">پس از ذخیره، همین صفحه باز می‌شود.</p>
            </div>
        </form>
    </section>

    @forelse($allVariants as $variant)
        <section class="admin-card p-5 sm:p-7">
            <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                <div>
                    <div class="flex items-center gap-3">
                        @if(in_array(strtolower($variant->type), ['color', 'colour', 'رنگ'], true) && $variant->color_hex)
                            <span class="h-7 w-7 rounded-full border border-black/10" style="background-color: {{ $variant->color_hex }};" aria-hidden="true"></span>
                        @endif
                        <h3 class="text-base font-bold text-[var(--admin-text)]">{{ $variant->name }}: {{ $variant->value }}</h3>
                        @if($variant->is_active)<span class="admin-badge admin-badge-success">فعال</span>@else<span class="admin-badge admin-badge-neutral">غیرفعال</span>@endif
                    </div>
                    <p class="mt-2 text-xs text-[var(--admin-muted)]">نوع: <span dir="ltr">{{ $variant->type }}</span> · {{ $variant->images->count() }} تصویر متصل</p>
                </div>
                <p class="text-xs text-[var(--admin-muted)]">ID: {{ $variant->id }}</p>
            </div>

            <form method="POST" action="{{ route('admin.product-variants.update', $variant) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="return_to_product" value="1">

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <label class="block text-xs text-[var(--admin-text)]">نوع / گروه
                        <input name="type" value="{{ $variant->type }}" list="variant-types" required maxlength="255" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                    <label class="block text-xs text-[var(--admin-text)]">نام ویژگی
                        <input name="name" value="{{ $variant->name }}" required maxlength="255" class="admin-input mt-2 w-full">
                    </label>
                    <label class="block text-xs text-[var(--admin-text)]">مقدار نمایش‌داده‌شده
                        <input name="value" value="{{ $variant->value }}" required maxlength="255" class="admin-input mt-2 w-full">
                    </label>
                    <label class="block text-xs text-[var(--admin-text)]">کد رنگ
                        <input name="color_hex" value="{{ $variant->color_hex }}" maxlength="20" class="admin-input mt-2 w-full" dir="ltr" placeholder="#202020">
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <label class="block text-xs text-[var(--admin-text)]">SKU (اختیاری)
                        <input name="sku" value="{{ $variant->sku }}" maxlength="255" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                    <label class="block text-xs text-[var(--admin-text)]">تغییر قیمت (تومان)
                        <input name="price_adjustment" type="number" value="{{ $variant->price_adjustment }}" step="0.01" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                    <label class="block text-xs text-[var(--admin-text)]">موجودی این تنوع
                        <input name="stock" type="number" min="0" value="{{ $variant->stock }}" required class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                </div>

                @if($productImages->isNotEmpty())
                    <div>
                        <p class="mb-3 text-xs font-semibold text-[var(--admin-text)]">تصاویر موجود متصل به این تنوع را انتخاب کنید</p>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($productImages as $image)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[var(--admin-border)] p-3 text-xs">
                                    <input type="checkbox" name="image_ids[]" value="{{ $image->id }}" @checked($variant->images->contains('id', $image->id)) class="admin-checkbox h-4 w-4">
                                    <span class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-[var(--admin-surface-soft)]">@if($image->url)<img src="{{ $image->url }}" alt="" class="h-full w-full object-cover">@endif</span>
                                    <span class="min-w-0"><span class="block truncate font-medium">{{ $image->alt ?: $product->name }}</span><span class="mt-1 block text-[10px] text-[var(--admin-muted)]">{{ $image->is_primary ? 'تصویر اصلی' : 'تصویر محصول' }}</span></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-xs text-[var(--admin-muted)]">هنوز تصویری برای محصول وجود ندارد؛ می‌توانید فایل‌های جدید همین رنگ را پایین آپلود کنید.</p>
                @endif

                <div class="rounded-xl border border-dashed border-[var(--admin-border)] p-4">
                    <label class="block text-xs font-semibold text-[var(--admin-text)]">افزودن عکس‌های جدید برای همین تنوع
                        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="admin-input mt-2 w-full p-2">
                    </label>
                    <p class="mt-2 text-[11px] leading-6 text-[var(--admin-muted)]">تصاویر جدید به محصول اضافه و به این تنوع متصل می‌شوند. تصاویر قبلی را با حذف تیک از اتصال این تنوع خارج کنید؛ فایل اصلی حذف نمی‌شود چون شاید برای رنگ دیگری هم استفاده شود.</p>
                    @error('images')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('images.*')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <label class="inline-flex items-center gap-2 text-xs text-[var(--admin-text)]">
                    <input type="checkbox" name="is_active" value="1" @checked($variant->is_active) class="admin-checkbox h-4 w-4">
                    این تنوع فعال و قابل انتخاب باشد
                </label>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="admin-btn admin-btn-primary">ذخیره تنوع و تصاویر</button>
                    <a href="{{ route('product.show', $product->slug) }}" target="_blank" rel="noopener" class="admin-btn admin-btn-ghost">مشاهده محصول در سایت</a>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.product-variants.destroy', $variant) }}" onsubmit="return confirm('این تنوع حذف شود؟ تصاویر باقی می‌مانند و اگر جایی دیگر استفاده شوند حذف نمی‌شوند.');" class="mt-3 border-t border-[var(--admin-border)] pt-4">
                @csrf
                @method('DELETE')
                <input type="hidden" name="return_to_product" value="1">
                <button type="submit" class="text-xs font-medium text-red-600 transition hover:text-red-700">حذف این تنوع</button>
            </form>
        </section>
    @empty
        <div class="admin-empty">
            <h3 class="text-sm font-semibold text-[var(--admin-text)]">هنوز تنوعی برای این محصول ثبت نشده است.</h3>
            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">فرم بالا را تکمیل کنید تا اولین رنگ، موجودی و تصاویر مربوط به آن اضافه شود.</p>
        </div>
    @endforelse
</div>
