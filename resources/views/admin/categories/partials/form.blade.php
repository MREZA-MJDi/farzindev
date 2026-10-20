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

            <div class="admin-image-preview aspect-square">

                <img
                    id="category-image-preview"
                    src="{{ $categoryImageUrl ?: '' }}"
                    alt="{{ $category->name ?? 'تصویر دسته‌بندی' }}"
                    class="admin-image {{ $categoryImageUrl ? '' : 'hidden' }}"
                >

                <div
                    id="category-image-placeholder"
                    class="{{ $categoryImageUrl ? 'hidden' : 'flex' }} h-full flex-col items-center justify-center p-6 text-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor"
                         class="mb-3 h-10 w-10 text-[var(--admin-muted)]">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 20.25 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8.25 8.25h.008v.008H8.25V8.25Z" />
                    </svg>

                    <span class="text-xs text-[var(--admin-muted)]">
                        {{ $editing ? 'تصویری برای این دسته‌بندی ثبت نشده است.' : 'هنوز تصویری انتخاب نشده است.' }}
                    </span>
                </div>

            </div>


            <div class="mt-5">

                <label for="image" class="admin-label">
                    انتخاب تصویر
                </label>

                <input
                    id="image"
                    name="image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="admin-input p-2"
                >

                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                    فرمت‌های مجاز: JPG، PNG و WEBP — حداکثر حجم تصویر ۲۰ مگابایت.
                </p>

                @error('image')
                <p class="mt-2 text-xs text-[var(--admin-danger)]">
                    {{ $message }}
                </p>
                @enderror

            </div>

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


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const imageInput =
            document.getElementById('image');

        const imagePreview =
            document.getElementById('category-image-preview');

        const imagePlaceholder =
            document.getElementById('category-image-placeholder');

        if (!imageInput || !imagePreview) {
            return;
        }

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

    });
</script>
@endpush
