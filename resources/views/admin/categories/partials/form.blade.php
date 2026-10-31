@csrf

@php
    $editing = isset($category);
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

    <div class="space-y-6 xl:col-span-2">

        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات اصلی
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    اطلاعات پایه دسته‌بندی را وارد کنید.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                <div class="sm:col-span-2">

                    <label for="name" class="admin-label">
                        نام دسته‌بندی
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $category->name ?? '') }}"
                        class="admin-input"
                        required
                    >

                    @error('name')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div class="sm:col-span-2">

                    <label for="slug" class="admin-label">
                        Slug
                    </label>

                    <input
                        id="slug"
                        name="slug"
                        type="text"
                        value="{{ old('slug', $category->slug ?? '') }}"
                        dir="ltr"
                        class="admin-input text-left"
                    >

                    <p class="mt-2 text-xs text-[var(--admin-muted)]">
                        برای URL دسته‌بندی استفاده می‌شود.
                    </p>

                    @error('slug')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div class="sm:col-span-2">

                    <label for="description" class="admin-label">
                        توضیحات
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        class="admin-textarea"
                    >{{ old('description', $category->description ?? '') }}</textarea>

                    @error('description')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

            </div>

        </div>


        <div class="admin-card p-6">
            <div class="mb-5">
                <h3 class="text-base font-bold text-[var(--admin-text)]">SEO اختصاصی دسته‌بندی</h3>
                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">این عنوان و توضیحات در صفحه عمومی دسته‌بندی استفاده می‌شوند و بر پیش‌فرض سراسری SEO اولویت دارند.</p>
            </div>

            <div class="space-y-4">
                <label for="meta_title" class="block text-sm font-medium text-[var(--admin-text)]">
                    عنوان SEO
                    <input id="meta_title" name="meta_title" type="text" maxlength="255" value="{{ old('meta_title', $category->meta_title ?? '') }}" class="admin-input mt-2" placeholder="خرید هود آشپزخانه | فرزین">
                    @error('meta_title')<span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>@enderror
                </label>

                <label for="meta_description" class="block text-sm font-medium text-[var(--admin-text)]">
                    توضیحات متا
                    <textarea id="meta_description" name="meta_description" rows="3" maxlength="320" class="admin-textarea mt-2" placeholder="توضیح کوتاه و دقیق درباره این دسته‌بندی...">{{ old('meta_description', $category->meta_description ?? '') }}</textarea>
                    @error('meta_description')<span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium text-[var(--admin-text)]">Canonical URL
                    <input name="canonical_url" type="url" inputmode="url" dir="ltr" maxlength="2048" value="{{ old('canonical_url', $category->canonical_url ?? '') }}" placeholder="https://farajihome.com/categories/..." class="admin-input mt-2 w-full">
                    @error('canonical_url')<span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium text-[var(--admin-text)]">قانون ربات‌ها
                    <select name="seo_robots" class="admin-select mt-2 w-full">
                        @foreach (['inherit' => 'ارث‌بری از سایت', 'index,follow' => 'Index, Follow', 'noindex,follow' => 'Noindex, Follow', 'noindex,nofollow' => 'Noindex, Nofollow'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('seo_robots', $category->seo_robots ?: 'inherit') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('seo_robots')<span class="mt-1 block text-xs text-[var(--admin-danger)]">{{ $message }}</span>@enderror
                </label>

                <label class="block text-sm font-medium text-[var(--admin-text)]">عنوان Open Graph
                    <input name="og_title" value="{{ old('og_title', $category->og_title ?? '') }}" maxlength="255" class="admin-input mt-2 w-full">
                </label>

                <label class="block text-sm font-medium text-[var(--admin-text)]">توضیحات Open Graph
                    <textarea name="og_description" rows="2" maxlength="320" class="admin-textarea mt-2 w-full">{{ old('og_description', $category->og_description ?? '') }}</textarea>
                </label>

                <p class="text-xs leading-6 text-[var(--admin-muted)]">پیشنهاد: عنوان منحصربه‌فرد و توضیح واقعی این دسته را بنویسید؛ متن تکراری بین دسته‌ها به سئو کمکی نمی‌کند.</p>
            </div>
        </div>


        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    نمایش و ترتیب
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    ترتیب و وضعیت نمایش دسته‌بندی را تعیین کنید.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                <div>

                    <label for="sort_order" class="admin-label">
                        ترتیب نمایش
                    </label>

                    <input
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        min="0"
                        value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                        class="admin-input"
                    >

                    @error('sort_order')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div class="flex items-end">

                    <label class="flex w-full cursor-pointer items-center gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-4 py-3">

                        <input
                            type="hidden"
                            name="is_active"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="admin-checkbox h-4 w-4"
                            @checked(old('is_active', $category->is_active ?? true))
                        >

                        <span>
                            <span class="block text-sm font-semibold text-[var(--admin-text)]">
                                دسته‌بندی فعال باشد
                            </span>

                            <span class="mt-1 block text-xs text-[var(--admin-muted)]">
                                دسته‌بندی در بخش فروشگاه قابل نمایش خواهد بود.
                            </span>
                        </span>

                    </label>

                </div>

            </div>

            @error('is_active')
            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                {{ $message }}
            </p>
            @enderror

        </div>

    </div>


    <div class="space-y-6">

        <div class="admin-card p-6">

            <div class="mb-5">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    تصویر دسته‌بندی
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    تصویر اصلی دسته‌بندی را انتخاب کنید.
                </p>
            </div>


            @php
                $categoryImageUrl = $editing
                    ? $category->image_url
                    : null;
            @endphp

            <div class="admin-image-preview mb-5 aspect-[4/3]">
                <img
                    id="category-image-preview"
                    data-image-editor-existing-preview
                    src="{{ $categoryImageUrl ?: '' }}"
                    alt="{{ $category->name ?? 'تصویر دسته‌بندی' }}"
                    class="admin-image {{ $categoryImageUrl ? '' : 'hidden' }}"
                >

                <div
                    id="category-image-placeholder"
                    data-image-editor-placeholder
                    class="{{ $categoryImageUrl ? 'hidden' : 'flex' }} h-full flex-col items-center justify-center rounded-2xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-6 text-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor"
                         class="mb-3 h-10 w-10 text-[var(--admin-muted)]">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z" />
                    </svg>
                    <span class="text-xs text-[var(--admin-muted)]">
                        {{ $editing ? 'تصویری برای این دسته‌بندی ثبت نشده است.' : 'هنوز تصویری انتخاب نشده است.' }}
                    </span>
                </div>
            </div>

            <label for="image" class="admin-label">
                {{ $editing ? 'تغییر تصویر' : 'انتخاب تصویر دسته‌بندی' }}
            </label>
            <input
                id="image"
                name="image"
                type="file"
                data-image-editor-input
                data-image-editor-default-ratio="4:3"
                data-image-editor-min-width="800"
                data-image-editor-min-height="600"
                accept="image/jpeg,image/png,image/webp"
                class="admin-input p-2"
            >

            <div data-image-editor-panel hidden class="mt-5 space-y-4 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                <strong class="block text-sm font-semibold text-[var(--admin-text)]">پیش‌نمایش و برش تصویر</strong>
                <canvas data-image-editor-canvas role="img" aria-label="پیش‌نمایش برش تصویر دسته‌بندی" class="mx-auto block max-w-full rounded-xl bg-[var(--admin-bg)]" style="width:min(100%,640px);height:auto;aspect-ratio:4/3;touch-action:none;cursor:grab;"></canvas>
                <p class="text-xs leading-6 text-[var(--admin-muted)]">تصویر را داخل کادر بکشید، نسبت را تغییر دهید و در صورت نیاز زوم یا چرخش کنید. خروجی نهایی دقیقاً مطابق همین پیش‌نمایش ذخیره می‌شود.</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <label class="block text-xs text-[var(--admin-text)]">نسبت کادر
                        <select data-image-editor-ratio class="admin-select mt-2">
                            <option value="1:1">مربع ۱:۱</option>
                            <option value="4:3" selected>استاندارد ۴:۳</option>
                            <option value="4:5">پرتره ۴:۵</option>
                            <option value="16:9">عریض ۱۶:۹</option>
                        </select>
                    </label>
                    <label class="block text-xs text-[var(--admin-text)]">بزرگ‌نمایی <span data-image-editor-zoom-label>۱×</span>
                        <input data-image-editor-zoom type="range" min="1" max="3" step="0.05" value="1" class="mt-3 w-full">
                    </label>
                    <div class="flex flex-wrap items-end gap-2">
                        <button type="button" data-image-editor-rotate="-90" class="admin-btn admin-btn-secondary">↶ چرخش</button>
                        <button type="button" data-image-editor-rotate="90" class="admin-btn admin-btn-secondary">چرخش ↷</button>
                        <button type="button" data-image-editor-reset class="admin-btn admin-btn-ghost">بازنشانی</button>
                    </div>
                </div>
                <p data-image-editor-status class="text-xs text-[var(--admin-muted)]" aria-live="polite">کادر را تنظیم کنید.</p>
            </div>

            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                فرمت‌های مجاز: JPG، PNG و WEBP؛ حداکثر حجم ۲۰ مگابایت. برای خروجی باکیفیت، تصویر حداقل ۸۰۰×۶۰۰ پیکسل باشد.
            </p>

            @error('image')
                <p role="alert" class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>
            @enderror

        </div>


        <div class="admin-card p-6">
            <div class="mb-5">
                <h3 class="text-base font-bold text-[var(--admin-text)]">پیش‌نمایش متحرک</h3>
                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">یک GIF سبک یا کلیپ MP4/WEBM را از کتابخانه انتخاب کن. فقط هنگام hover یا لمس بارگذاری و حداکثر ۵ ثانیه پخش می‌شود.</p>
            </div>
            <label for="hover_media_id" class="admin-label">GIF یا ویدیوی کوتاه</label>
            <select id="hover_media_id" name="hover_media_id" class="admin-input">
                <option value="">بدون پیش‌نمایش متحرک</option>
                @foreach(($motionMedia ?? collect()) as $media)
                    <option value="{{ $media->id }}" @selected((string) old('hover_media_id', $category->hover_media_id ?? '') === (string) $media->id)>
                        {{ $media->is_video ? 'ویدیو' : 'GIF' }} · {{ $media->original_name }} ({{ number_format($media->size / 1024 / 1024, 1) }} MB)
                    </option>
                @endforeach
            </select>
            @error('hover_media_id')
                <p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>
            @enderror
            @if(($motionMedia ?? collect())->isEmpty())
                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">هنوز GIF یا ویدیوی مناسب ثبت نشده؛ از <a class="font-semibold underline" href="{{ route('admin.media.create') }}">افزودن رسانه</a> یک GIF حداکثر ۱۰ یا ویدیوی MP4/WEBM حداکثر ۳۰ مگابایت آپلود کن.</p>
            @endif
        </div>


        <div class="admin-card p-6">

            <div class="mb-5">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    ذخیره
                </h3>
            </div>

            <div class="flex flex-col gap-3">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary w-full"
                >
                    {{ $editing ? 'ذخیره تغییرات' : 'ایجاد دسته‌بندی' }}
                </button>

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="admin-btn admin-btn-secondary w-full"
                >
                    انصراف
                </a>

            </div>

        </div>

    </div>

</div>



